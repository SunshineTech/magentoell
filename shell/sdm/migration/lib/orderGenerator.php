<?php

class OrderGenerator
{
    const CURRENCY_US = 'USD';
    const CURRENCY_UK_GBP = 'GBP';
    const CURRENCY_UK_EUR = 'EUR';

    protected $_shippingMethod = 'freeshipping_freeshipping';
    protected $_paymentMethod = 'cashondelivery';
    protected $_order = null;
    protected $_storeId = null;
    protected $_orderNumber = null;
    protected $_payment = null;
    protected $_paymentCode = null;
    protected $_paymentName = null;

    protected $_shippingMapping = array(
        'STANDARD' => 'Standard',
        'GROUND' => 'Ground',
        'INTERNATIONAL_ECONOMY' => 'International Economy',
        'INTERNATIONAL_ECONOMY_FREIGHT' => 'International Economy Freight',
        'FEDEX_GROUND' => 'FedEx Ground',
        'COD' => 'COD',
        'INTERNATIONAL_PRIORITY' => 'International Priority',
        'INTERNATIONAL_PRIORITY_FREIGHT' => 'International Priority Freight'
    );

    /**
     * These much match the current Magento order statuses and states.
     *
     * @var array
     */
    protected $_statusMapping = array(
        'Cancelled' => array('state' => 'canceled', 'status' => 'canceled'),
        'Shipped' => array('state' => 'complete', 'status' => 'complete'),
        'Refunded' => array('state' => 'closed', 'status' => 'closed'),
        'In Process' => array('state' => 'processing', 'status' => 'inprocess'),
        'Pending' => array('state' => 'processing', 'status' => 'pending_review'),
        'Processing' => array('state' => 'processing', 'status' => 'processing'),
        'New' => array('state' => 'processing', 'status' => 'new'),
        'Open' => array('state' => 'processing', 'status' => 'open')
    );

    /**
     * Set customer
     *
     * @param Mage_Customer_Model_Customer|int $customer
     */
    public function setCustomer($customer)
    {
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $this->_customer = $customer;
        } elseif (is_numeric($customer)) {
            $this->_customer = Mage::getModel('customer/customer')->load($customer);
        } else {
            return false;
        }

        return $this;
    }

    /**
     * Sets the shipping method information. Migrated orders get "imported"
     * shipping code, which is a dummy method (doesn't exist).
     *
     * @param str $code
     */
    public function setShippingMethod($code)
    {
        if (isset($this->_shippingMapping[$code])) {
            $methodName = $this->_shippingMapping[$code];
        } else {
            $methodName = 'Standard';   // If not available for some reason
        }

        $this->_shippingDescription = $methodName;
        return $this;
    }

    /**
     * Sets the payment type. Migrated orders get "Check/Money Order" method.
     *
     * @param str $name
     */
    public function setPaymentMethod($name)
    {
        $this->_paymentCode = 'checkmo';
        $this->_paymentName = $name;
        return $this;
    }

    /**
     * Prepare the totals
     *
     * @param stdClass $data
     */
    public function setTotals($data)
    {
        // echo "Shipping amt: {$data->shipping_amount}" . PHP_EOL;
        if ($data->system == 'szuk') {
            /**
             * UK shipping includes VAT
             * Note: Ellison DB shipping amount does not include VAT and must be calculated.
             */
            if (!isset($data->vat_percentage)) {
                $data->vat_percentage = 0;
            }

            $this->_shipping = $data->shipping_amount;
            $this->_tax = $data->tax_amount;

            // Calculated tax must be checked against tax exempt status
            if ($data->vat_exempt) {
                $this->_shippingTax = 0;
            } else {
                $this->_shippingTax = round($data->shipping_amount * ($data->vat_percentage)/100 , 2);
            }

        } else {
            $this->_shipping = $data->shipping_amount;
            $this->_shippingTax = 0;
            $this->_tax = $data->tax_amount;
        }

        $this->_subtotal = $data->subtotal_amount;
        $this->_surcharge = $data->handling_amount;

        $this->_discount = $data->total_discount;   // Does not go into the grand total

        // Grand total is a calculated field in Ellison
        $this->_grandTotal = $this->_subtotal + $this->_shipping
            + $this->_tax + $this->_shippingTax + $this->_surcharge;

        return $this;
    }

    /**
     * Creates an order. Requries a customer to be set but assume all required data
     * have been set for the migration.
     *
     *
     * @param array $products
     * @param stdClass $data
     *
     * @return null
     */
    public function createOrder($products, $data)
    {
        if (!($this->_customer instanceof Mage_Customer_Model_Customer)) {
            $this->setCustomer(null);
        }

        // Get some properties and variables populated
        $this->_storeId = $this->_getStoreId($data->system, $data->locale);
        $paymentMethod = '';
        if (isset($data->payment['payment_method'])) {
            $paymentMethod = $data->payment['payment_method'];
        }
        $this->setShippingMethod($data->shipping_service)
            ->setPaymentMethod($paymentMethod)
            ->setTotals($data);

        $this->_order = Mage::getModel('sales/order')
            ->setIncrementId($data->order_number)
            ->setStoreId($this->_storeId)
            // ->setData('store_id', $this->_storeId)
            ->setGlobalCurrencyCode(self::CURRENCY_US)  // always USD
            ->setQuoteId(0)
            ->setCreatedAt($data->created_at)
            ->setUpdatedAt($data->updated_at);  // updated_at gets overwritten when object is saved

        // Set currencies
        if ($currencyCode = $this->_isUk($this->_storeId)) {
            $this->_order->setBaseCurrencyCode(self::CURRENCY_UK_GBP)
                ->setStoreCurrencyCode(self::CURRENCY_UK_GBP)
                ->setOrderCurrencyCode($currencyCode);
        } else {
            $this->_order->setBaseCurrencyCode(self::CURRENCY_US)
                ->setStoreCurrencyCode(self::CURRENCY_US)
                ->setOrderCurrencyCode(self::CURRENCY_US);
        }

        $this->_order->setCustomerEmail($this->_customer->getEmail())
            ->setCustomerFirstname($this->_customer->getFirstname())
            ->setCustomerLastname($this->_customer->getLastname())
            ->setCustomerGroupId($this->_customer->getGroupId())
            ->setCustomerIsGuest(0)
            ->setCustomer($this->_customer);

        /**
         * Process addresses
         */
        // Note these are placeholder addresses; actuall billing and shipping
        // address will be updated post-launch.
        $billing = $this->_customer->getDefaultBillingAddress();
        $shipping = $this->_customer->getDefaultShippingAddress();
        if (!$billing && $shipping) {
            $billing = $shipping;
        } elseif (!$shipping && $billing) {
            $shipping = $billing;
        }

        // Orders require sales/order_address
        $billingAddress = Mage::getModel('sales/order_address');
        if ($billing) {
            $billingAddress->setStoreId($this->_storeId)
                ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_BILLING)
                ->setCustomerId($this->_customer->getId())
                ->setCustomerAddressId($billing->getEntityId())
                ->setPrefix($billing->getPrefix())
                ->setFirstname($billing->getFirstname())
                ->setMiddlename($billing->getMiddlename())
                ->setLastname($billing->getLastname())
                ->setSuffix($billing->getSuffix())
                ->setCompany($billing->getCompany())
                ->setStreet($billing->getStreet())
                ->setCity($billing->getCity())
                ->setCountryId($billing->getCountryId())
                ->setRegion($billing->getRegion())
                ->setRegionId($billing->getRegionId())
                ->setPostcode($billing->getPostcode())
                ->setTelephone($billing->getTelephone())
                ->setFax($billing->getFax());
        } else {
            $billingAddress->setCompany('Address not available');
        }
        $this->_order->setBillingAddress($billingAddress);

        $shippingAddress = Mage::getModel('sales/order_address');
        if ($shipping) {
            $shippingAddress->setStoreId($this->_storeId)
                ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)
                ->setCustomerId($this->_customer->getId())
                ->setCustomerAddressId($shipping->getEntityId())
                ->setPrefix($shipping->getPrefix())
                ->setFirstname($shipping->getFirstname())
                ->setMiddlename($shipping->getMiddlename())
                ->setLastname($shipping->getLastname())
                ->setSuffix($shipping->getSuffix())
                ->setCompany($shipping->getCompany())
                ->setStreet($shipping->getStreet())
                ->setCity($shipping->getCity())
                ->setCountryId($shipping->getCountryId())
                ->setRegion($shipping->getRegion())
                ->setRegionId($shipping->getRegionId())
                ->setPostcode($shipping->getPostcode())
                ->setTelephone($shipping->getTelephone())
                ->setFax($shipping->getFax());
        } else {
            $shippingAddress->setCompany('Address not available');
        }

        // Surchage
        $shippingAddress->setBaseSdmShippingSurcharge($this->_surcharge);
        $shippingAddress->setSdmShippingSurcharge($this->_surcharge);

        $this->_order->setShippingAddress($shippingAddress);
        $this->_order->setShippingMethod($this->_shippingMethod)
            ->setShippingDescription($this->_shippingDescription);

        // Add payment
        $payment = Mage::getModel('sales/order_payment')
            ->setStoreId($this->_storeId)
            ->setCustomerPaymentId(0)
            ->setMethod($this->_paymentCode);
            // $this->_paymentName  // Added to comment instead
        $this->_order->setPayment($payment);

        /**
         * Store-dependent data; however, all conversion rates are 1:1 for Ellison
         * and store prices are customized.
         */
        $this->_order->setBaseToOrderRate(1)
            ->setBaseToGlobalRate(1)
            ->setStoreToOrderRate(1)
            ->setStoreToBaseRate(1);

        /**
         * Add order items
         */
        $itemsData = $this->_addProducts($products);
        $this->_order->setTotalQtyOrdered($itemsData->totalQty);
        $this->_order->setWeight($itemsData->totalWeight);

        /**
         * Misc.
         */
        $this->_order->setIsVirtual(0)
            ->setShippingTaxAmount(0)
            ->setBaseShippingTaxAmount(0)
            ->setShippingDiscountAmount(0)
            ->setBaseShippingDiscountAmount(0)
            ->setCustomerNoteNotify(0)
            ->setEmailSent(0)
            ->setRemoteIp('127.0.0.1')
            // ->setStoreName('Migrated')   // Doesn't work
            ;

        /**
         * Status-dependent data. setData() used for better readability.
         */
        if ($data->status == 'Shipped') {
            $this->_order->setData('total_paid', $this->_grandTotal)
                ->setData('base_total_paid', $this->_grandTotal)
                ->setData('total_due', 0)
                ->setData('base_total_due', 0)
                // Below required for accurate reporting
                ->setData('base_total_invoiced', $this->_grandTotal)
                ->setData('total_invoiced', $this->_grandTotal)
                ->setData('base_tax_invoiced', $this->_tax)
                ->setData('tax_invoiced', $this->_tax)
                ->setData('base_shipping_invoiced', $this->_shipping)
                ->setData('shipping_invoiced', $this->_shipping)
                ->setData('base_total_refunded', null)
                ->setData('total_refunded', null)
                ->setData('base_tax_refunded', null)
                ->setData('tax_refunded', null)
                ->setData('base_shipping_refunded', null)
                ->setData('shipping_refunded', null)
                ->setData('discount_invoiced', $this->_discount)
                ->setData('base_discount_invoiced', $this->_discount)
                ->setData('shipping_tax_amount', $this->_shippingTax)
                ->setData('base_shipping_tax_amount', $this->_shippingTax)
                ->setData('subtotal_invoiced', $this->_subtotal)
                ->setData('base_subtotal_invoiced', $this->_subtotal)
                ->setData('total_invoiced_cost', 0)
                ->setData('base_total_invoiced_cost', 0)
                ->setData('hidden_tax_amount', 0)
                ->setData('base_hidden_tax_amount', 0)
                ->setData('shipping_hidden_tax', 0)
                ->setData('base_shipping_hidden_tax', 0)
                ->setData('hidden_tax_invoiced', 0)
                ->setData('base_hidden_tax_invoiced', 0);

        } elseif ($data->status == 'Refunded') {
            // It hasn't been fully defined what to do with these orders
            $this->_order->setData('total_paid', $this->_grandTotal)
                ->setData('base_total_paid', $this->_grandTotal)
                ->setData('total_due', 0)
                ->setData('base_total_due', 0)
                // Below required for accurate reporting
                ->setData('base_total_invoiced', $this->_grandTotal)
                ->setData('total_invoiced', $this->_grandTotal)
                ->setData('base_tax_invoiced', $this->_tax)
                ->setData('tax_invoiced', $this->_tax)
                ->setData('base_shipping_invoiced', $this->_shipping)
                ->setData('shipping_invoiced', $this->_shipping)
                ->setData('base_total_refunded', $this->_grandTotal)
                ->setData('total_refunded', $this->_grandTotal)
                ->setData('base_tax_refunded', $this->_tax)
                ->setData('tax_refunded', $this->_tax)
                ->setData('base_shipping_refunded', $this->_shipping)
                ->setData('shipping_refunded', $this->_shipping);

        } elseif ($data->status == 'Cancelled') {
            // It hasn't been fully defined what to do with these orders
            $this->_order->setData('total_paid', null)
                ->setData('base_total_paid', null)
                ->setData('total_due', $this->_grandTotal)
                ->setData('base_total_due', $this->_grandTotal)
                // Below required for accurate reporting
                ->setData('base_total_invoiced', null)
                ->setData('total_invoiced', null)
                ->setData('base_tax_invoiced', null)
                ->setData('tax_invoiced', null)
                ->setData('base_shipping_invoiced', null)
                ->setData('shipping_invoiced', null)
                ->setData('base_total_refunded', null)
                ->setData('total_refunded', null)
                ->setData('base_tax_refunded', null)
                ->setData('tax_refunded', null)
                ->setData('base_shipping_refunded', null)
                ->setData('shipping_refunded', null);
        }

        // Set status
        if (isset($this->_statusMapping[$data->status])) {
            $state = $this->_statusMapping[$data->status]['state'];
            $status = $this->_statusMapping[$data->status]['status'];
        } else {
            $state = 'complete';
            $status = 'complete';
        }
        $this->_order->setData('status', $status);
        $this->_order->setData('state', $state);

        // Set totals/universal amounts
        // Must be set at the end; otherwise, they get unset for some reason
        $this->_order->setSubtotal($this->_subtotal)
            ->setBaseSubtotal($this->_subtotal)
            ->setSubtotalInclTax($this->_subtotal + $this->_tax)
            ->setBaseSubtotalInclTax($this->_subtotal + $this->_tax)
            ->setShippingAmount($this->_shipping)
            ->setBaseShippingAmount($this->_shipping)
            ->setShippingInclTax($this->_shipping + $this->_tax)
            ->setBaseShippingInclTax($this->_shipping + $this->_tax)
            ->setTaxAmount($this->_tax + $this->_shippingTax)
            ->setBaseTaxAmount($this->_tax + $this->_shippingTax)
            ->setDiscountAmount($this->_discount)
            ->setBaseDiscountAmount($this->_discount)
            ->setGrandTotal($this->_grandTotal)
            ->setBaseGrandTotal($this->_grandTotal);
        $this->_order->setData('shipping_incl_tax', $this->_shipping + $this->_shippingTax)
            ->setData('base_shipping_incl_tax', $this->_shipping + $this->_shippingTax);

        // Comment
        $this->_order->addStatusHistoryComment(
            "Migrated order. Payment used: {$this->_paymentName}"
        );

        $this->_order->save();
    }

    /**
     * Wrapper to add order items
     *
     * @return stdClass
     */
    protected function _addProducts($products)
    {
        $data = new stdClass;
        $data->totalQty = 0;
        $data->totalWeight = 0; // Not available form Ellison orders

        foreach ($products as $product) {
            $this->_addProduct($product);
            $data->totalQty += $product['qty'];
        }

        return $data;
    }

    /**
     * Adds a dummy product as an order item
     *
     * @param array $product
     */
    protected function _addProduct($product)
    {
        $rowTotal = $product['qty'] * $product['price'];

        $item = Mage::getModel('sales/order_item')
            ->setStoreId($this->_storeId)
            ->setQuoteItemId(0)
            ->setQuoteParentItemId(NULL)
            ->setProductId(null)
            ->setProductType('simple')
            ->setQtyBackordered(NULL)
            ->setTotalQtyOrdered($product['qty'])
            ->setQtyOrdered($product['qty'])
            ->setName($product['name'])
            ->setSku($product['sku'])
            ->setPrice($product['price'])
            ->setBasePrice($product['price'])
            ->setOriginalPrice($product['price'])
            ->setRowTotal($rowTotal)
            ->setBaseRowTotal($rowTotal)
            ->setWeeeTaxApplied(serialize(array()))
            ->setBaseWeeeTaxDisposition(0)
            ->setWeeeTaxDisposition(0)
            ->setBaseWeeeTaxRowDisposition(0)
            ->setWeeeTaxRowDisposition(0)
            ->setBaseWeeeTaxAppliedAmount(0)
            ->setBaseWeeeTaxAppliedRowAmount(0)
            ->setWeeeTaxAppliedAmount(0)
            ->setWeeeTaxAppliedRowAmount(0);

        // Tax info available only for SZUK orders
        if ($this->_isUk($this->_storeId)) {
            if (isset($product['tax_amount']) && isset($product['tax_percent'])) {
                $item->setTaxAmount($product['tax_amount'])
                    ->setBaseTaxAmount($product['tax_amount'])
                    ->setTaxPercent($product['tax_percent']);
            }
        }

        $this->_order->addItem($item);
    }

    /**
     * Determines if an order is from the UK site and return the currency code
     * if it is.
     *
     * @param int $storeId
     * @return str|bool
     */
    protected function _isUk($storeId)
    {
        if ($storeId == 4 || $storeId == 7) {
            if ($storeId == 4) {
                return self::CURRENCY_UK_EUR;
            } else {
                return self::CURRENCY_UK_GBP;
            }
        }

        return false;
    }

    /**
     * Returns the Magento store ID given Ellison order's locale and sysmte code
     *
     * @param str $locale
     * @param str $system
     *
     * @return int
     */
    protected function _getStoreId($system, $locale)
    {
        if ($system === 'szus') {
            return 1;
        } elseif ($system === 'eeus') {
            return 6;
        } elseif ($system === 'erus') {
            return 5;
        } elseif ($system === 'szuk') {
            if ($locale === 'en-UK') {
                return 7;
            } elseif ($locale === 'en-EU') {
                return 4;
            } else {
                return 0;   // Should not happen
            }
        } else {
            return 0;   // Should not happen
        }

        return 0;
    }
}
