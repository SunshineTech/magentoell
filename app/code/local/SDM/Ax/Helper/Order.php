<?php
/**
 * Separation Degrees One
 *
 * Ellison's AX ERP integration
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Ax
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Ax_Helper_Order class
 */
class SDM_Ax_Helper_Order extends SDM_Ax_Helper_Data
{
    /**
     * These are all lowercase for comparison
     */
    const XML_VALUE_SHIPPED = 'shipped';
    const XML_VALUE_INPROCESS = 'in process';
    const XML_VALUE_CANCELLED = 'cancelled';

    /**
     * AX ERP payment_method node values
     */
    const AX_PAYMENT_METHOD_CARD = 'CC';
    const AX_PAYMENT_METHOD_PAYPAL = 'Paypal';
    const AX_PAYMENT_METHOD_GIFTCARD = 'GC';
    const AX_PAYMENT_METHOD_PURCHASE_ORDER = 'Terms';

    const FEDEX_HOME_DELIVERY = 'fedex_GROUND_HOME_DELIVERY';
    const FEDEX_GROUND = 'fedex_GROUND_HOME_DELIVERY';
    const FEDEX_INTL_GROUND = 'fedex_INTERNATIONAL_GROUND';
    const FEDEX_INTL_ECON = 'fedex_INTERNATIONAL_ECONOMY';
    const FEDEX_INTL_PRIORITY = 'fedex_INTERNATIONAL_PRIORITY';

    const PRINT_CATALOG_ATTRIBUTE_SET_NAME = 'Print Catalog';

    /**
     * EAV option value for product_type
     *
     * @var str
     */
    const GIFT_CARD_PRODUCT_TYPE = 'Gift_Card';

    /**
     * Store IDs and codes
     *
     * @var array
     */
    protected $_stores = array();

    /**
     * Associative array with store codes and IDs
     *
     * @var array
     */
    protected $_ellisonStoreCodes = array();

    /**
     * Associative array with website codes and IDs
     *
     * @var array
     */
    protected $_storeToWebsite = array();

    /**
     * Global DOMDocument object
     *
     * @var DOMDocument
     */
    protected $_dom = null;

    /**
     * Region ID to code mapping
     *
     * @var array
     */
    protected $_regionMapping = array();

    /**
     * Military and non-contiguous state region codes
     *
     * @var array
     */
    protected $_nonContiguousRegions = array();

    /**
     * EAV option ID for product_type "Gift Card"
     *
     * @var int
     */
    protected $_giftCardOptionId = null;

    /**
     * "Print Catalog" attribute set ID
     *
     * @var int
     */
    protected $_printCatalogAttSetId = null;

    /**
     * Initialize
     */
    public function __construct()
    {
        parent::__construct();

        // Store Print Catalog attribute set ID for comparison later
        $attSet = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->addFieldToFilter('attribute_set_name', self::PRINT_CATALOG_ATTRIBUTE_SET_NAME)
            ->getFirstItem();
        if ($attSet && $attSet->getId()) {
            $this->_printCatalogAttSetId = $attSet->getId();
        }
    }

    /**
     * Order XML export --------------------------------------------------------
     */

    /**
     * Create an XML file of orders from all sites, except UK, for AX to pick up
     *
     * @param array $codes
     * @param bool  $isUk
     *
     * @return bool
     */
    public function exportXml($codes, $isUk = false)
    {
        if (!$this->isEnabled()) {
            $this->log('Order XML cannot be created wbhile AX ERP Extension is disabled.');
            return;
        }

        $this->log('>>> START: Order XML export');

        $this->_init(); // Initialize variables required repeatedly
        $processedOrders = array(); // have order entity IDs, not increment IDs
        $todaysDate = Mage::getModel('core/date')->date('m/d/Y');

        // Get relevant order collection
        $collection = $this->_getCollection($codes);

        // Create XML
        $this->_dom = new DOMDocument('1.0', 'UTF-8');
        $bodyNode = $this->_dom->appendChild($this->_dom->createElement('salesorders'));
        $i = 0;

        foreach ($collection as $order) {
            $this->log('Processing #' . $order->getIncrementId());

            $isUk = false;
            if ($this->_stores[$order->getStoreId()] === SDM_Core_Helper_Data::STORE_CODE_UK_EU
                || $this->_stores[$order->getStoreId()] === SDM_Core_Helper_Data::STORE_CODE_UK_BP
            ) {
                $isUk = true;
            }

            $orderNode = $this->_dom->createElement('order');
            $headerNode = $orderNode->appendChild($this->_dom->createElement('header'));

            $customer = $this->_getCustomer($order);
            $couponCode = $order->getCouponCode();

            $this->_addToNode($headerNode, 'sales_id', $this->_getSalesOrigin($order) . $order->getIncrementId());
            $this->_addToNode($headerNode, 'ax_customer', $this->_getAxAccountId($order));
            $this->_addToNode($headerNode, 'invoice_account', $this->_getInvoiceAccountId($order));
            $this->_addToNode($headerNode, 'cust_name', $customer->getFirstname() . ' ' . $customer->getLastname());
            $this->_addToNode($headerNode, 'currency_code', $this->_getCurrencyCode($order));
            $this->_addToNode($headerNode, 'email', $customer->getEmail());
            $this->_addToNode($headerNode, 'source_code', $couponCode);
            $this->_addToNode($headerNode, 'sales_origin', $this->_getSalesOrigin($order));
            $this->_addToNode($headerNode, 'giftcard_order', $this->_hasGiftCardInOrder($order));

            // ->payment
            $paymentNode = $headerNode->appendChild($this->_dom->createElement('payment'));
            $this->_addToNode($paymentNode, 'freight_charges', $this->_getFreightCharge($order));
            $this->_addToNode($paymentNode, 'surcharge', $order->getShippingAddress()->getSdmShippingSurcharge());

            // ->->tax (UK only)
            if ($isUk) {
                $taxNode = $paymentNode->appendChild($this->_dom->createElement('tax'));
                $this->_addToNode($taxNode, 'tax_amount', $order->getTaxAmount());
                $this->_addToNode($taxNode, 'VAT_percentage', $this->_getVatPercentage($order));
            }

            // ->->payment_type
            try {
                $this->_addPaymentType($paymentNode, $order);
            } catch (Exception $e) {
                // Log error but continue order
                $this->log('Payment write error: ' . $e->getMessage());
            }

            // ->invoice_address
            $billingAddress = $order->getBillingAddress();
            $street1 = implode(' ', $billingAddress->getStreet());
            $shippingAddress = $order->getShippingAddress();
            $street2 = implode(' ', $shippingAddress->getStreet());

            $invoiceNode = $paymentNode->appendChild($this->_dom->createElement('invoice_address'));

            if ($isUk) {
                $stateCode1 = $billingAddress->getRegion();
                $stateCode2 = $shippingAddress->getRegion();
            } else {
                if (isset($this->_regionMapping[$billingAddress->getRegionId()])) {
                    $stateCode1 = $this->_regionMapping[$billingAddress->getRegionId()];
                } else {
                    $stateCode1 = $billingAddress->getRegion();
                }
                if (isset($this->_regionMapping[$shippingAddress->getRegionId()])) {
                    $stateCode2 = $this->_regionMapping[$shippingAddress->getRegionId()];
                } else {
                    $stateCode2 = $shippingAddress->getRegion();
                }
            }

            $this->_addToNode($invoiceNode, 'invoice_contact_first', $billingAddress->getFirstname());
            $this->_addToNode($invoiceNode, 'invoice_contact_last', $billingAddress->getLastname());
            $this->_addToNode($invoiceNode, 'invoice_company', $billingAddress->getCompany());
            $this->_addToNode($invoiceNode, 'street', $street1);
            $this->_addToNode($invoiceNode, 'zip_code', $billingAddress->getPostcode());
            $this->_addToNode($invoiceNode, 'city', $billingAddress->getCity());
            $this->_addToNode($invoiceNode, 'state', $stateCode1);
            $this->_addToNode($invoiceNode, 'country', $billingAddress->getCountryId());
            $this->_addToNode($invoiceNode, 'phone_num', $billingAddress->getTelephone());

            // ->delivery
            $deliveryNode = $headerNode->appendChild($this->_dom->createElement('delivery'));
            $this->_addToNode($deliveryNode, 'delivery_zone', $this->_getDeliveryZone($shippingAddress));  // Not required for now
            $this->_addToNode($deliveryNode, 'delivery_mode', $this->_getDeliveryMode($order));
            $this->_addToNode($deliveryNode, 'delivery_term', $this->_getDeliveryTerm($order));
            $this->_addToNode($deliveryNode, 'priority', $this->_getDeliveryPriority($order));
            $this->_addToNode($deliveryNode, 'delivery_contact_first', $shippingAddress->getFirstname());
            $this->_addToNode($deliveryNode, 'delivery_contact_last', $shippingAddress->getLastname());

            $deliveryAddressNode = $deliveryNode->appendChild($this->_dom->createElement('delivery_address'));
            $this->_addToNode($deliveryAddressNode, 'ship_date', $todaysDate);
            $this->_addToNode($deliveryAddressNode, 'ship_company', $shippingAddress->getCompany());
            $this->_addToNode($deliveryAddressNode, 'street', $street2);
            $this->_addToNode($deliveryAddressNode, 'zip_code', $shippingAddress->getPostcode());
            $this->_addToNode($deliveryAddressNode, 'city', $shippingAddress->getCity());
            $this->_addToNode($deliveryAddressNode, 'state', $stateCode2);
            $this->_addToNode($deliveryAddressNode, 'country', $shippingAddress->getCountryId());
            $this->_addToNode($deliveryAddressNode, 'ship_phone', $shippingAddress->getTelephone());

            // ->order_comments
            $this->_addToNode($headerNode, 'order_comments');

            // Order items
            $linesNode = $orderNode->appendChild($this->_dom->createElement('lines'));
            $items = $order->getAllVisibleItems();
            $k = 0;
            foreach ($items as $item) {
                $k++;
                $itemNode = $linesNode->appendChild($this->_dom->createElement('line'));
                $numAtt = $this->_dom->createAttribute('num');
                $numAtt->value = $k;
                $skuAtt = $this->_dom->createAttribute('item_number');
                $skuAtt->value = $item->getSku();
                $qtyAtt = $this->_dom->createAttribute('qty');
                $qtyAtt->value = (int)$item->getQtyOrdered();
                $vatAtt = $this->_dom->createAttribute('VAT_amount');

                if ($isUk) {
                    $vatAtt->value = $item->getTaxAmount();
                } else {
                    $vatAtt->value = '0.00';
                }

                $upsellAtt = $this->_dom->createAttribute('upsell');
                $upsellAtt->value = '';

                // MSRP/Original price
                $unitPrice = $this->_getUnitPrice($item);
                $unitPriceAtt = $this->_dom->createAttribute('unit_price');
                $unitPriceAtt->value = $unitPrice;

                // Discount
                $discount = $this->_getDiscount($item, $unitPrice);
                $discountAtt = $this->_dom->createAttribute('discount_amount');
                $discountAtt->value = $discount;

                $itemNode->appendChild($numAtt);
                $itemNode->appendChild($skuAtt);
                $itemNode->appendChild($qtyAtt);
                $itemNode->appendChild($unitPriceAtt);
                $itemNode->appendChild($discountAtt);
                $itemNode->appendChild($vatAtt);
                $itemNode->appendChild($upsellAtt);
            }

            $bodyNode->appendChild($orderNode);
            $processedOrders[$i]['id'] = $order->getId();
            $processedOrders[$i]['number'] = $order->getIncrementId();

            $i++;
        }

        // Prepare for pretty output
        $this->_dom->preserveWhiteSpace = false;
        $this->_dom->formatOutput = true;
        // echo $this->_dom->saveXML(); die;

        // Write XML. Archive in this method since path is generated there.
        $result = $this->_writeOrderXMLToFile($processedOrders, $isUk);
        if (!$result) {
            return false;
        }

        $this->log('>>> END: Order XML export');
        $this->log('*******************************************');
        $this->log('');

        return true;
    }

    /**
     * Write to file the XML DOM document
     *
     * @param array $processedOrders
     * @param bool  $isUk
     *
     * @return bool
     */
    protected function _writeOrderXMLToFile($processedOrders, $isUk)
    {
        if (empty($processedOrders)) {
            $this->log('There are no orders to write.');
            return true;
        }

        if ($isUk) {
            $savePath = Mage::getBaseDir() . DS . $this->getExportPath('uk');
            $archivePath = Mage::getBaseDir() . DS . $this->getOrderExportArchivePath('uk');
        } else {
            $savePath = Mage::getBaseDir() . DS . $this->getExportPath();
            $archivePath = Mage::getBaseDir() . DS . $this->getOrderExportArchivePath();
        }

        $fileName = $this->getFileName('order_export');
        $fullPath = $savePath . DS . $fileName;
        $fullArchivePath = $archivePath . DS . $fileName;
        $fileAdapter = new Varien_Io_File();

        // Create directory if necessary
        if (!file_exists($savePath)) {
            $result1 = $fileAdapter->mkdir($savePath, SDM_Ax_Helper_Data::FILE_PERMISSION);
            if (!$result1) {
                $this->log(
                    "Failed to create directory: $savePath. Order statuses were "
                        . "not changed. Order XML export aborted.",
                    Zend_Log::CRIT
                );
                return false;
            }
        }

        // Write file
        $fileAdapter->open();   // Warning gets thrown without this
        $errorMessage = "Failed to write file: $fullPath. Order statuses were "
                . "not changed. Order XML export aborted.";

        try {
            // This could throw an exception. e.g. permission issue, etc.
            $result2 = $fileAdapter->write(
                $fullPath,
                $this->_dom->saveXML(),
                SDM_Ax_Helper_Data::FILE_PERMISSION
            );
        } catch (Exception $e) {
            $this->log($errorMessage, Zend_Log::CRIT);
            return false;
        }

        if (!$result2) {
            $this->log($errorMessage, Zend_Log::CRIT);
            return false;
        }

        // Update orders only after writing the file
        $this->_updateOrders($processedOrders);

        // Archive file
        if (!file_exists($archivePath)) {
            $result1 = $fileAdapter->mkdir($archivePath, SDM_Ax_Helper_Data::FILE_PERMISSION);
            if (!$result1) {
                $this->log(
                    "Failed to create directory: $savePath. Order file $fileName"
                        . 'could not be archived',
                    Zend_Log::CRIT
                );
                return false;
            }
        }
        copy($fullPath, $fullArchivePath);

        return true;
    }

    /**
     * Returns the AX's delivery method name
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return str
     */
    protected function _getDeliveryMode($order)
    {
        $axCode = 'FXGround';

        $shipping = $order->getShippingAddress();

        // Each website has different shipping options
        $websiteCode = $this->_storeToWebsite[$order->getStoreId()];
        if ($websiteCode === SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_US) {
            if ($this->_isPpDelivery($order)) {
                $axCode = 'PP';
            } else {
                $axCode = $this->_getFedExAxCode($order);
            }
        } elseif ($websiteCode === SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_UK) {
            $axCode = 'DH';

        } elseif ($websiteCode === SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_RE) {
            if ($this->_isPpDelivery($order)) {
                $axCode = 'PP';
            } else {
                $axCode = $this->_getFedExAxCode($order);
            }
        } elseif ($websiteCode === SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_ED) {
            if ($this->_isPpDelivery($order)) {
                $axCode = 'PP';
            }
        }

        return $axCode;
    }

    /**
     * Return the USPS delivery zone
     *
     * @param Mage_Sales_Model_Order_Address $shipping
     *
     * @return int|str
     */
    protected function _getDeliveryZone($shipping)
    {
        $zipcode = $shipping->getPostcode();
        $zoneCode = Mage::helper('sdm_uspszone')->getZoneCode($zipcode);

        if ($zoneCode) {
            return $zoneCode;
        } else {
            return '';
        }
    }

    /**
     * Returns the AX code corresponding to the Fed Ex shipping method used
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return str
     */
    protected function _getFedExAxCode($order)
    {
        $axCode = 'FXGround';
        $code = $order->getShippingMethod();

        switch ($code) {
            case self::FEDEX_HOME_DELIVERY:
                $axCode = 'FXGround';
                break;
            case self::FEDEX_HOME_DELIVERY:
                $axCode = 'FXGround';
                break;
            case self::FEDEX_INTL_GROUND:
                $axCode = 'FDXINTGRD';
                break;
            case self::FEDEX_INTL_PRIORITY:
                $axCode = 'FDXIP';
                break;
            case self::FEDEX_INTL_ECON:
                $axCode = 'FDXIE';
                break;
        }

        return $axCode;
    }

    /**
     * Checks for PP orders. See ELSN-562 for requirement.
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool
     */
    protected function _isPpDelivery($order)
    {
        $shipping = $order->getShippingAddress();
        $streets = $shipping->getStreet();
        $street1 = $streets[0];

        // Check if order has a gift card
        if ($order->getHasGiftCardInOrder()) {  // This is set in _hasGiftCardInOrder()
            return true;
        }

        // PO Box regex: http://stackoverflow.com/questions/5159535/po-box-validation
        if (preg_match('!p(ost)?\.?\s*o(ffice)?\.?(box|\s|$)!i', $street1)) {
            return true;
        }

        // Check for military and Hawaii regions (HI/AK/AA/AP/AE)
        if ($shipping->getCountryId() === 'US') {
            if (isset($this->_regionMapping[$shipping->getRegionId()])
                && in_array($this->_regionMapping[$shipping->getRegionId()], $this->_nonContiguousRegions)
            ) {
                return true;
            }
        }

        // Check if all items are print catalogs
        $allPrintCatalogs = true;   // Set it to true initially
        foreach ($order->getAllVisibleItems() as $item) {
            if (!$this->_isPrintCatalog($item)) {
                $allPrintCatalogs = false;  // If at least one item is not, break out
                break;
            }
        }
        if ($allPrintCatalogs) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the attribute set is "Print Catalog"
     *
     * @param Mage_Sales_Model_Order_Item $item
     *
     * @return bool
     */
    protected function _isPrintCatalog($item)
    {
        if ($item->getProduct()->getAttributeSetId() == $this->_printCatalogAttSetId) {
            return true;
        }

        return false;
    }

    /**
     * Returns the delivery term code
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return str
     */
    protected function _getDeliveryTerm($order)
    {
        $code = 'PP';
        if ($this->_stores[$order->getStoreId()] === SDM_Core_Helper_Data::STORE_CODE_ER) {
            $code = 'PP'; // Possibly @todo: no COD method yet for ERUS ("CC")
        }

        return $code;
    }

    /**
     * Returns the delivery priority string
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return str
     */
    protected function _getDeliveryPriority($order)
    {
        $code = 'Normal'; // Only Normal for now

        return $code;
    }

    /**
     * Returns the approprirate shipping amount.
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return double
     */
    protected function _getFreightCharge($order)
    {
        $shippingAmount = $order->getShippingAmount();

        // Mahavi said this was needed, then said it wasn't next day.
        /*if ($this->_stores[$order->getStoreId()] === SDM_Core_Helper_Data::STORE_CODE_UK_EU
            || $this->_stores[$order->getStoreId()] === SDM_Core_Helper_Data::STORE_CODE_UK_BP
        ) {
            $vat = (double)$this->_getVatPercentage($order);
            $shippingAmount -= round($shippingAmount*$vat/100, 2);
        }*/

        return (string)$shippingAmount;
    }

    /**
     * Returns the VAT percentage for UK only
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return str
     */
    protected function _getVatPercentage($order)
    {
        if ($this->_stores[$order->getStoreId()] === SDM_Core_Helper_Data::STORE_CODE_UK_EU
            || $this->_stores[$order->getStoreId()] === SDM_Core_Helper_Data::STORE_CODE_UK_BP
        ) {
            foreach ($order->getAllVisibleItems() as $item) {
                $vat = (int)$item->getTaxPercent();
                return (string)$vat;
            }
        }

        return '';
    }

    /**
     * Determine if the order contains a gift card product.
     *
     * @param Mage_Sale_Model_Order $order
     *
     * @return str
     */
    protected function _hasGiftCardInOrder($order)
    {
        foreach ($order->getAllVisibleItems() as $item) {
            $itemType = $item->getItemType();
            if (!$itemType) {   // In case it failed to write to the order item table
                $itemType = $item->getProduct()->getProductType();
            }
            if (!$itemType) {
                $product = Mage::getModel('catalog/product')->load($item->getProduct()->getId());
                $itemType = $product->getProductType();
            }

            if ($itemType == $this->_giftCardOptionId) {
                $order->setHasGiftCardInOrder(true);
                return 'TRUE';
            }
        }

        return 'FALSE';
    }

    /**
     * There are largely two types of discounts that can be applied to a given
     * order item.
     *
     * 1. A catalog level discount, such as negotiated, special
     *    discount, Special taxonomy, etc. discounts.
     * 2. A shopping cart level discount by entering a coupon
     *
     * This method returns the discount effectively applied to this order item,
     * whether it's from the aforementioned 1 or 2 discount.
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @param double                      $msrp
     *
     * @return double
     */
    protected function _getDiscount($item, $msrp)
    {
        if (!$msrp) {
            $msrp = $this->_getUnitPrice($item);
        }

        $soldPrice = $item->getPrice();
        $discount = ($msrp - $soldPrice) + $item->getDiscountAmount()/$item->getQtyOrdered();  // Discount types 1 + 2

        if ($discount < 0) {    // Cannot have negative discount amount
            $discount = 0;
        }

        return number_format((float)$discount, 2, '.', '');
    }

    /**
     * Returns the MSRP/original price of the order item. "MSRP" is for all
     * non-ERUS sites. In ERUS, it's called "Wholesale". All  data are  from
     * Magento's attribute 'price'.
     *
     * If MSRP is not saved in the order, look up the product directly.
     *
     * @param Mage_Sales_Model_Order_Item $item
     *
     * @return int
     */
    protected function _getUnitPrice($item)
    {
        if ($item->getMsrp()) {
            $msrp = $item->getMsrp();
        } else {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $msrp = $product->getPrice();
        }

        return number_format((float)$msrp, 2, '.', '');
    }

    /**
     * Returns the respective currency code
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return int
     */
    protected function _getCurrencyCode($order)
    {
        $storeCode = $this->_stores[$order->getStoreId()];
        $code = 'USD';

        if ($storeCode == SDM_Core_Helper_Data::STORE_CODE_UK_BP) {
            $code = 'GBP';
        } elseif ($storeCode == SDM_Core_Helper_Data::STORE_CODE_UK_EU) {
            $code = 'EUR';
        } elseif ($storeCode == SDM_Core_Helper_Data::STORE_CODE_US
            || $storeCode == SDM_Core_Helper_Data::STORE_CODE_ER
            || $storeCode == SDM_Core_Helper_Data::STORE_CODE_EE
        ) {
            $code = 'USD';
        }

        return $code;
    }

    /**
     * Returns the sales origin code
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return int
     */
    protected function _getSalesOrigin($order)
    {
        // Get website (capitalized) code given store ID
        return strtoupper($this->_ellisonStoreCodes[$this->_storeToWebsite[$order->getStoreId()]]);
    }

    /**
     * Update the order statuses for orders that have been written.
     *
     * @param Array $ordersIds
     *
     * @return void
     */
    protected function _updateOrders($ordersIds)
    {
        foreach ($ordersIds as $ids) {
            try {
                Mage::getModel('sales/order')->load($ids['id'])
                    ->setStatus(SDM_Sales_Helper_Data::ORDER_STATUS_CODE_PROCESSING)
                    ->save();
                $this->log("Order status updated #{$ids['number']}.");

            } catch (Exception $e) {
                $this->log(
                    "Failed update order #{$ids['number']}'s status to '"
                        . SDM_Sales_Helper_Data::ORDER_STATUS_LABEL_INPROCESS . "'.",
                    Zend_Log::ERR
                );
            }
        }
    }

    /**
     * Add the payment_type node. An order can have only one payment per type,
     * but number of types applied depends on the website. See below.
     *
     * Types of applicable payments
     * ERUS: Purchase order only or Cybersource only
     * EEUS: Purchase order only or Cybersource and/or GC
     * SZUS: Combination of Cybersource, giftcard, and/or PayPal Credit
     * SZUK: Sage Pay only
     *
     * @param DOMDocument            $node
     * @param Mage_Sales_Model_Order $order
     *
     * @return null
     */
    protected function _addPaymentType($node, $order)
    {
        // Returns an array of payment codes
        // @see SDM_Sales_Helper_Data constants
        $paymentsUsed = Mage::helper('sdm_sales')->getPaymentsUsed($order);

        if (empty($paymentsUsed)) {
            throw new Exception(
                'No payment method detected for order #' . $order->getIncrementId()
            );
        }

        $paymentTypeNode = $node->appendChild($this->_dom->createElement('payment_type'));
        foreach ($paymentsUsed as $used) {
            $typeNode = $paymentTypeNode->appendChild($this->_dom->createElement('type'));

            if ($used === SDM_Sales_Helper_Data::PAYMENT_TYPE_CODE_CYBERSOURCE
                || $used === SDM_Sales_Helper_Data::PAYMENT_TYPE_CODE_CYBERSOURCE_SFC
            ) {  // CC
                $payment = $this->_getPayment($order);
                $cybersource = $this->_getCybersourceData($order);

                $this->_addToNode($typeNode, 'payment_method', self::AX_PAYMENT_METHOD_CARD);
                $this->_addToNode($typeNode, 'card_type', $this->_getAxCardTypeCybersource($payment));
                if (isset($cybersource['request_id'])) {
                    $this->_addToNode($typeNode, 'request_id', $cybersource['request_id']);
                } else {
                    // $this->log('Order # ' . $order->getIncrementId() . ': ');
                }
                if (isset($cybersource['token'])) {
                    $this->_addToNode($typeNode, 'cybersource_token', $cybersource['token']);
                }
                $this->_addToNode($typeNode, 'cybersource_merchant_ref_num', $order->getId());
                $this->_addToNode(
                    $typeNode,
                    'name_on_card',
                    $order->getBillingAddress()->getFirstname() . ' '
                        . $order->getBillingAddress()->getLastname()
                );
                // From document: "A if Authorized.  This is part of Cybersource response"
                $this->_addToNode($typeNode, 'transaction_type', 'A');  // Always Authorize with this payment
                $this->_addToNode($typeNode, 'amount_charged', $payment->getAmountOrdered());

            } elseif ($used === SDM_Sales_Helper_Data::PAYMENT_TYPE_CODE_SAGEPAY) {   // Sage Pay
                $payment = $this->_getPayment($order);

                $this->_addToNode($typeNode, 'payment_method', self::AX_PAYMENT_METHOD_CARD);
                $this->_addToNode($typeNode, 'card_type', $this->_getAxCardTypeSagePay($payment));
                $this->_addToNode($typeNode, 'request_id', '');
                $this->_addToNode($typeNode, 'cybersource_token', '');
                $this->_addToNode($typeNode, 'cybersource_merchant_ref_num', $order->getId());
                $this->_addToNode(
                    $typeNode,
                    'name_on_card',
                    $order->getBillingAddress()->getFirstname() . ' '
                        . $order->getBillingAddress()->getLastname()
                );
                $this->_addToNode($typeNode, 'transaction_type', '');
                $this->_addToNode($typeNode, 'amount_charged', $payment->getAmountPaid());

            } elseif ($used === SDM_Sales_Helper_Data::PAYMENT_TYPE_CODE_PAYPAL) {   // PayPal Express
                $payment = $this->_getPayment($order);

                $this->_addToNode($typeNode, 'payment_method', self::AX_PAYMENT_METHOD_PAYPAL);
                $this->_addToNode($typeNode, 'card_type', self::AX_PAYMENT_METHOD_PAYPAL);
                $this->_addToNode($typeNode, 'request_id', $payment->getLastTransId());
                $this->_addToNode($typeNode, 'paypal_ref_num', $order->getIncrementId());
                $this->_addToNode($typeNode, 'amount_charged', $payment->getAmountAuthorized());
                $this->_addToNode($typeNode, 'transaction_type', 'A');

                // PAYPAL CREDIT NOT DONE; METHOD CODE UNKNOWN

                // GIFT CARD PAYMENT NOT FINISHED
            } elseif ($used === SDM_Sales_Helper_Data::PAYMENT_TYPE_CODE_GIFTCARD) {   // giftcard
                $giftcard = Mage::helper('sdm_valutec')->getGiftcard($order);
                $this->_addToNode($typeNode, 'payment_method', self::AX_PAYMENT_METHOD_GIFTCARD);
                if ($giftcard) {
                    $this->_addToNode($typeNode, 'giftcard_num', $giftcard['number'] . '=' . $giftcard['pin']);
                    $this->_addToNode($typeNode, 'giftcard_identifier', $giftcard['identifier']);
                    $this->_addToNode($typeNode, 'giftcard_auth_num',  $giftcard['authorization_code']);
                } else {
                    $this->log('Order # ' . $order->getIncrementId() . ': Gift card information not available.');
                }
                $this->_addToNode($typeNode, 'giftcard_amount_charged',  $order->getSdmValutecGiftcardAmount());

            } elseif ($used === SDM_Sales_Helper_Data::PAYMENT_TYPE_CODE_PURCHASE_ORDER) {
                $payment = $this->_getPayment($order);

                $this->_addToNode($typeNode, 'payment_method', self::AX_PAYMENT_METHOD_PURCHASE_ORDER);
                $this->_addToNode($typeNode, 'request_id', $payment->getPoNumber());
                $this->_addToNode($typeNode, 'amount_charged', $payment->getAmountOrdered());

            } elseif ($used === SDM_Sales_Helper_Data::PAYMENT_TYPE_CODE_FREE) {
                // Remove the entire <payment_type> node
                // "free" when for print catalog orders and GC orders ,but GC orders are
                // separately identified.
                $paymentTypeNode->parentNode->removeChild($paymentTypeNode);

            } else {
                throw new Exception(
                    'Unable to process order #' . $order->getIncrementId()
                        . '. Payments used detected (' . implode(',', $paymentsUsed)
                        . ') but unable to write <type> node.'
                );
            }
        }
    }

    /**
     * Returns the Cybersource token that AX needs
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return str
     */
    protected function _getCybersourceData($order)
    {
        $data = array();
        $storeId = $order->getStoreId();
        $payment = $order->getPayment();

        // SZUK does not use Cybersource
        if ($this->_stores[$storeId] === SDM_Core_Helper_Data::STORE_CODE_US
            || $this->_stores[$storeId] === SDM_Core_Helper_Data::STORE_CODE_EE
        ) {
            $data['token'] = $payment->getCybersourceToken();
            $data['request_id'] = $payment->getLastTransId();
        } elseif ($this->_stores[$storeId] === SDM_Core_Helper_Data::STORE_CODE_ER) {
            $info = $payment->getAdditionalInformation();
            if (isset($info['request_token'])) {
                $data['token'] = $info['request_token'];
            } else {
                $this->log('Order # ' . $order->getIncrementId() . ': Request token not available');
            }
            if (isset($info['request_id'])) {
                $data['request_id'] = $info['request_id'];
            } else {
                $this->log('Order # ' . $order->getIncrementId() . ': Request ID not available.');
            }
        }

        return $data;
    }

    /**
     * Returns the card_ype node value of the order
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     *
     * @return str|null
     */
    protected function _getAxCardTypeCybersource($payment)
    {
        switch ($payment->getCcType()) {
            case 'VI':
                $type = 'Visa';
                break;
            case 'MC':
                $type = 'MasterCard';
                break;
            case 'AE':
                $type = 'AmericanExpress';
                break;
            case 'DI':
                $type = 'Discover';
                break;
            default:
                $type = null;
        }

        return $type;
    }

    /**
     * Returns the card_ype node value of the order
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     *
     * @return str|null
     */
    protected function _getAxCardTypeSagePay($payment)
    {
        switch ($payment->getCcType()) {
            case 'VI':
                $type = 'visa';
                break;
            case 'MC':
                $type = 'master';
                break;
            case 'DELTA':
                $type = 'delta';
                break;
            case 'MAESTRO':
                $type = 'maestro';
                break;
            case 'UKE':
                $type = 'electron';
                break;
            case 'SOLO':
                $type = 'solo';
                break;
            default:
                $type = null;
        }

        return $type;
    }

    /**
     * Returns required customer information
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return Mage_Sales_Model_Order_Payment
     */
    protected function _getPayment($order)
    {
        $payment = Mage::getResourceModel('sales/order_payment_collection')
                ->setOrderFilter($order)
                ->getFirstItem();

        return $payment;
    }
    /**
     * Return the collection of orders for the given stores.
     *
     * @param array $codes Store codes
     *
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    protected function _getCollection($codes)
    {
        $map = array_flip($this->_stores);
        $storeIds = array();
        foreach ($codes as $code) {
            $storeIds[] = $map[$code];  // Get store IDs
        }

        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('status', SDM_Sales_Helper_Data::ORDER_STATUS_CODE_OPEN)
            ->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_PROCESSING)
            ->addFieldToFilter('store_id', array('in' => $storeIds))
            // ->addFieldToFilter('increment_id', '100000035')  // For testing
            ;
        // Mage::log($collection->getSelect()->__toString());

        return $collection;
    }

    /**
     * Returns required customer information
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer($order)
    {
        $customer = Mage::getResourceModel('customer/customer_collection')
            ->addAttributeToSelect(array('firstname','lastname','store_id', 'ax_customer_id'))
            ->addAttributeToFilter('entity_id', $order->getCustomerId())
            ->getFirstItem();

        if ($this->_stores[$order->getStoreId()] == SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_US) {
            $customer->setAxCustomerId('Sizzix.com');   // Override just for US site
        }

        return $customer;
    }

    /**
     * Adds a child to the given element/node
     *
     * @param DOMDocument $node
     * @param str         $nodeName
     * @param str         $content
     *
     * @return void
     */
    protected function _addToNode($node, $nodeName, $content = null)
    {
        // Remove some characters that break the XML
        if ($content) {
            $content = str_replace('&', '', $content);
            $content = str_replace('<', '', $content);
            $content = str_replace('>', '', $content);
        }
        $node->appendChild(
            $this->_dom->createElement($nodeName, $content)
        );

        // If CDATA is ever required, use below.
        /*if ($isCdata) {   // Inlcude variable in the method argument
            $addedNode = $node->appendChild($this->_dom->createElement($nodeName));
            $addedNode->appendChild($this->_dom->createCDATASection($content));
        } else {
            $node->appendChild(
                $this->_dom->createElement($nodeName, $content)
            );
        }*/
    }

    /**
     * Get the customer's AX account ID. If EEUS + card payment, return the
     * static AX ID for guest.
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return int
     */
    protected function _getAxAccountId($order)
    {
        $websiteCode = $this->_storeToWebsite[$order->getStoreId()];

        if ($websiteCode === SDM_Core_Helper_Data::WEBSITE_CODE_US) {
            return 'Sizzix.com';
        } elseif ($websiteCode === SDM_Core_Helper_Data::WEBSITE_CODE_ED) {
            return $this->getEeusAxAccountId($order);
        } else {
            if ($order->getAxAccountId()) {
                return $order->getAxAccountId();
            } else {
                return 'New';
            }
        }
    }

    /**
     * Get the order's invoice ID. If not available, return the customer's
     * AX account number. If EEUS + card payment, return the
     * static AX ID for guest.
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return int
     */
    protected function _getInvoiceAccountId($order)
    {
        $websiteCode = $this->_storeToWebsite[$order->getStoreId()];

        if ($websiteCode === SDM_Core_Helper_Data::WEBSITE_CODE_US) {
            return 'Sizzix.com';
        } elseif ($websiteCode === SDM_Core_Helper_Data::WEBSITE_CODE_ED) {
            return $this->getEeusInvoiceAccountId($order);
        } else {
            $invoiceId = trim($order->getInvoiceAccountId());
            if (!$invoiceId) {
                return $this->_getAxAccountId($order);
            }
            return $invoiceId;
        }
    }

    /**
     * Set up arrays for getting website and store information
     * for repeated lookups
     *
     * @return void
     */
    protected function _init()
    {
        // Website-store-related
        $collection = Mage::getModel('core/store')->getCollection();
        foreach ($collection as $store) {
            $this->_stores[$store->getId()] = $store->getCode();
            $this->_storeToWebsite[$store->getId()] = $store->getWebsite()->getCode();
        }
        // print_r($this->_stores); die;
        $this->_ellisonStoreCodes = $this->getEllisonSystemCodes();

        // Region-related
        $collection = Mage::getModel('directory/region')->getCollection();
        foreach ($collection as $region) {
            $this->_regionMapping[$region->getId()] = $region->getCode();
        }

        $this->_nonContiguousRegions = array('AF', 'AA', 'AC', 'AE', 'AM', 'AP', 'HI', 'AK');

        $attribute = Mage::getSingleton('eav/config')
            ->getAttribute('catalog_product', 'product_type');
        if ($attribute->usesSource()) { // Check if it has options
            $options = $attribute->getSource()->getAllOptions(false);
            foreach ($options as $opt) {
                if (strtolower($opt['label']) === strtolower(self::GIFT_CARD_PRODUCT_TYPE)) {
                    $this->_giftCardOptionId = $opt['value'];
                    break;
                }
            }
        }
    }

    /**
     * Order status update -----------------------------------------------------
     */

    /**
     * Processes order status updates
     *
     * @return bool
     */
    public function processStatusUpdate()
    {
        if (!$this->isEnabled()) {
            $this->log('Order statuses cannot be updated when AX ERP Extension is disabled');
            return;
        }

        $this->log('*******************************************');
        $this->log('>>> START: Order status update.');

        // Get all of XML files
        $filePath = $this->getImportPath('order_status');
        $files = glob(Mage::getBaseDir() . DS . $filePath . DS . '*.xml');
        $filesToArchive = array();

        // Update all orders
        $dom = new DOMDocument();
        foreach ($files as $file) {
            $this->log('Processing: ' . basename($file));
            // Process only order status files
            $fileLowerCase = strtolower(basename($file));
            if (strpos($fileLowerCase, 'orders_status_') === false) {
                continue;
            }

            // Read XML file
            $dom->load($file);
            // echo $dom->saveXML(); die;

            $orders = $dom->getElementsByTagName('order');  // Get all 'order' nodes
            foreach ($orders as $orderNode) {
                try {
                    $this->_updateOrderStatus($orderNode);
                } catch (Exception $e) {
                    $this->log(
                        'Unable to update ' . $orderNode->getAttribute('number')
                            . '. Error: ' . $e->getMessage()
                    );
                }

            }
            // echo $dom->saveXML();
            $filesToArchive[] = $file;
        }

        // Archive/move files
        $result = $this->archiveFiles($filesToArchive, 'order_status');
        if (!$result) {
            return false;
        }

        $this->log('>>> END: Order status update');
        $this->log('*******************************************');
        $this->log('');

        return true;
    }

    /**
     * Updates the order status. Updates the states, if necessary.
     *
     * Important:
     * In general, AX status update request are always enforced, except for
     * "In Process" acknowledgement.
     *
     * @param  str $orderNode
     * @throws Mage_Core_Exception
     *
     * @return bool
     */
    protected function _updateOrderStatus($orderNode)
    {
        // Parse the XML node
        $orderNumber = $orderNode->getAttribute('number');
        $statusOrg = trim($orderNode->getAttribute('status'));
        $status = strtolower($statusOrg);
        $incrementId = substr($orderNumber, 4, strlen($orderNumber) - 4);

        $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
        if (!$order->getId()) {
             $this->log("#$incrementId does not exist.", Zend_Log::WARN);
            return false;
        }

        // $status values are from AX
        if ($status == 'in process') {  // AX's acknowlegment
            // Only update it if order is currently in "Processing"
            if ($order->getStatus() == SDM_Sales_Helper_Data::ORDER_STATUS_CODE_PROCESSING) {
                $order->setStatus(SDM_Sales_Helper_Data::ORDER_STATUS_CODE_INPROCESS)
                    ->save();
                $this->log(
                    "$$incrementId updated to '"
                        . SDM_Sales_Helper_Data::ORDER_STATUS_LABEL_INPROCESS . "'."
                );

            } else {
                $this->log(
                    "Unabled to update #$incrementId to '"
                        . SDM_Sales_Helper_Data::ORDER_STATUS_LABEL_INPROCESS
                        . "'. Not in '"
                        . SDM_Sales_Helper_Data::ORDER_STATUS_LABEL_PROCESSING . "'.",
                    Zend_Log::WARN
                );
                return false;
            }

        } elseif ($status == 'shipped') {
            $this->_completeOrder($order, $orderNode);

        } elseif ($status == 'cancelled') {
            $this->_cancelOrder($order);

        } else {
            $this->log("Unable to update #$incrementId. '$statusOrg' not recognized.");
            return false;
        }
        // Add more as necessary

        return true;
    }

    /**
     * Cancels or closes an order
     *
     * Note: Orders can be canceled if it hasn't be paid for.
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return void
     */
    protected function _cancelOrder($order)
    {
        // Cancel this order
        if ($order->canCancel()) {
            $order->cancel()->save();

            if ($order->getState() != Mage_Sales_Model_Order::STATE_CANCELED) {
                $order->setState(Mage_Sales_Model_Order::STATE_CANCELED)
                    ->setStatus(Mage_Sales_Model_Order::STATE_CANCELED);
            }
            $order->addStatusHistoryComment('Canceled by AX')
                ->save();
            $this->log("#{$order->getIncrementId()} canceled.");

            // Close this order
            // Should not issue refunds because all automatically created invoices are dummies
        } elseif ($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) {
            $this->_forceClose($order);

            // Rest would need to be forecefully closed as well. e.g. paid but unshipped orders
        } else {
            $this->_forceClose($order);
        }
    }

    /**
     * Ships the order fully and updates it to 'complete' status. Paymet is captured
     * in AX. Makes a dummy offline invoice.
     *
     * @param Mage_Sales_Model_Order $order
     * @param DOMElement             $orderNode
     *
     * @return void
     */
    protected function _completeOrder($order, $orderNode)
    {
        if ($order->getState() === Mage_Sales_Model_Order::STATE_CLOSED
            || $order->getState() === Mage_Sales_Model_Order::STATE_CANCELED
            || $order->getState() === Mage_Sales_Model_Order::STATE_COMPLETE
        ) {
            $this->_forceShip($order);
            return;
        }

        $incrementId = $order->getIncrementId();

        if ($order->canShip()) {
            $axCarrierCode = trim($orderNode->getAttribute('carrier_description'));
            $trackingNumber = trim($orderNode->getAttribute('tracking_number'));
            $trackingLink = trim($orderNode->getAttribute('tracking_url'));

            // Create a shipment
            $shipment = Mage::getModel('sales/service_order', $order)
                ->prepareShipment();    // ship all items

            // Tracking object takes an array of data
            $trackingData = array(
                'carrier_code' => 'custom',
                'title' => $axCarrierCode,
                'number' => $trackingNumber,
                'description' => $trackingLink
            );
            $track = Mage::getModel('sales/order_shipment_track')
                ->addData($trackingData);
            $shipment->addTrack($track);
            $shipment->register();

            $shipment->getOrder()->setIsInProcess(true);    // Required
            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($shipment)
                ->addObject($shipment->getOrder())
                ->save();

            $shipment->sendEmail()
                // ->setEmailSent(true)->save() // works without these
                ;
        }

        if ($order->canInvoice()) {
            // Create an offline invoice for record in Magento
            $invoice = Mage::getModel('sales/service_order', $order)
                ->prepareInvoice(); // invoice all items
            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
            $invoice->addComment(
                'This is a dummy/offline invoice created for record',
                false,
                false
            );  // don't notify customer, show on frontend
            $invoice->register();

            $invoice->getOrder()->setIsInProcess(true);

            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();
            // Don't send email
        }

        // At this point, the order should be in state 'complete', regardless of
        // whether shipment and invoice were created in this specific invocation.
        if ($order->getState() !== Mage_Sales_Model_Order::STATE_COMPLETE) {
            $this->_forceShip($order);
        } else {
            $this->log("#$incrementId shipped.");
        }
    }

    /**
     * Force-ships/completes an order and leaves a comment
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return void
     */
    protected function _forceShip($order)
    {
        $comment = 'Set to '. SDM_Sales_Helper_Data::ORDER_STATUS_LABEL_SHIPPED
            . ' by AX. Could not create invoice/shipment since order was already'
            . ' Complete, Closed, or Canceled in Magento.';

        $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE)
            ->setData('status', Mage_Sales_Model_Order::STATE_COMPLETE)
            ->save();
        $order->addStatusHistoryComment($comment)   // Returns history object
            ->save();

        $this->log("#{$order->getIncrementId()} force-shipped.");
    }

    /**
     * Force closes an order and leaves a comment
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return void
     */
    protected function _forceClose($order)
    {
        $comment = 'Closed by AX';

        if ($order->getState() === Mage_Sales_Model_Order::STATE_CLOSED) {
            $this->log("#{$order->getIncrementId()} already closed.");
            return;
        } elseif ($order->getState() === Mage_Sales_Model_Order::STATE_CANCELED) {
            $this->log("#{$order->getIncrementId()} already canceled. Closed skipped.");
            return;
        }

        $order->setData('state', Mage_Sales_Model_Order::STATE_CLOSED)
            ->setData('status', Mage_Sales_Model_Order::STATE_CLOSED)
            ->save();
        $order->addStatusHistoryComment($comment)   // Returns history object
            ->save();

        $this->log("#{$order->getIncrementId()} closed.");
    }
}
