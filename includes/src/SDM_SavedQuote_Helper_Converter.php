<?php
/**
 * Separation Degrees Media
 *
 * Allows saving quotes that can be later be converted into orders with preserved
 * pricing.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_SavedQuote
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * Converts quotes to savedquotes
 */
class SDM_SavedQuote_Helper_Converter extends SDM_Core_Helper_Data
{
    /**
     * Set some persistent variables
     */
    public function __construct()
    {
        $this->_logFile = Mage::getStoreConfig(SDM_SavedQuote_Helper_Data::XML_PATH_LOGGING_FILE_NAME);
    }

    /**
     * Remove saved quote from session
     *
     * @return $this
     */
    public function removeSavedQuoteFromSession()
    {
        if (Mage::helper('savedquote')->isSavedQuoteSession()) {
            try {
                $this->_clearCurrentQuote();
            } catch (Exception $e) {
                Mage::getSingleton('core/session')->addError($e->getMessage());
            }
        }
        return $this;
    }

    /**
     * Prepares a Magento quote given a saved quote
     *
     * @param SDM_SavedQuote_Model_Savedquote $savedQuote
     * @param boolean                         $keepExistingQuote
     *
     * @return Mage_Sales_Model_Quote
     * @throws Mage_Core_Exception
     */
    public function addSavedQuoteToSession($savedQuote, $keepExistingQuote = false)
    {
        // Do we do this to a new quote, or the existing quote?
        if ($keepExistingQuote) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();

            // Remove any existing items since we'll be re-adding them later
            $quoteItems = $quote->getAllItems();
            foreach ($quoteItems as $item) {
                $quote->removeItem($item->getId());
            }
        } else {
            $this->_clearCurrentQuote();

            $quote = Mage::getModel('sales/quote')->setStoreId($this->getStoreId());
            $quote->assignCustomer($this->getCustomer());
            $quote->setSavedQuoteId($savedQuote->getIncrementId());
        }


        // Add items
        $preOrders = array();
        foreach ($savedQuote->getItemCollection() as $item) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $qty = new Varien_Object(array('qty' => $item->getQty()));

            // Set flag/saved price
            $product->setUseSavedPrice(true);   // Flag
            $product->setCustomPrice($item->getPrice());            // Saved price
            $product->setOriginalCustomPrice($item->getPrice());    // Saved price
            if (($item->getPreOrderShippingDate() && !$item->getPreOrderReleaseDate()) || $item->getIsPreOrder()) {
                $preOrders[$item->getProductId()] = array(
                    $item->getPreOrderReleaseDate(),
                    $item->getPreOrderShippingDate()
                );
            }
            $product->setIsPreOrder($item->getIsPreOrder());

            // @todo: Products MUST be validated and make sure they are available to purchase
            $quote->addProduct($product, $qty);
        }

        // Recalculate row totals
        foreach ($quote->getAllVisibleItems() as $item) {
            $item->calcRowTotal();
            $item->setPreOrderApproved(true);
            if (isset($preOrders[$item->getProductId()])) {
                $item->setPreOrderReleaseDate($preOrders[$item->getProductId()][0])
                    ->setPreOrderShippingDate($preOrders[$item->getProductId()][1])
                    ->setIsPreOrder(true);
            }
        }
        $quote->save();

        if (!Mage::helper('sdm_preorder')->isQuotePreOrder($quote)) {
            // Add addresses
            $addressData = $savedQuote->getShippingAddress()->getData();

            // Remove conflicting attributes
            unset($addressData['address_id']);
            unset($addressData['same_as_billing']);
            unset($addressData['address_type']);

            $quote->getShippingAddress()->addData($addressData);

            // Don't reset billing address if we're keeping the same quote
            if (!$keepExistingQuote) {
                $quote->getBillingAddress()->addData($addressData);
            }

            // Shipping method
            // Regardless of what the customer chose, choose the custom method
            $targetShippingCode = SDM_Shipping_Model_Carrier_Savedquote::CARRIER_CODE
                . '_' . SDM_Shipping_Model_Carrier_Savedquote::METHOD_STANDARD;

            $quote->getShippingAddress()
                ->setCollectShippingRates(true)
                ->collectShippingRates()
                ->setShippingMethod($targetShippingCode);

            // Override shipping price and method name (only name)
            $rates = $quote->getShippingAddress()->collectShippingRates()
                ->getGroupedAllShippingRates();
            foreach ($rates as $carrier) {
                foreach ($carrier as $rate) {
                    if ($rate->getCode() == $targetShippingCode) {
                        // Apply saved shipping price and update name to original method
                        $rate->setCarrierTitle($savedQuote->getShippingMethod());
                        $rate->setPrice($savedQuote->getShippingCost());
                        $rate->save();
                    }
                }
            }
        }

        // Set shipping surcharge + override
        if ($savedQuote->getSdmShippingSurcharge() > 0) {
            $quote->setSdmShippingSurchargeOverride($savedQuote->getSdmShippingSurcharge());
        }

        $quote->collectTotals();
        $quote->setIsActive(true);
        $quote->save();

        return $quote;
    }

    /**
     * Prepares a Magento quote given a saved quote
     *
     * @param SDM_SavedQuote_Model_Savedquote $savedQuote
     *
     * @return Mage_Sales_Model_Quote
     * @throws Mage_Core_Exception
     */
    public function updateShippingMethodFromQuote($savedQuote)
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        // Shipping method
        // Regardless of what the customer chose, choose the custom method
        $targetShippingCode = SDM_Shipping_Model_Carrier_Savedquote::CARRIER_CODE
            . '_' . SDM_Shipping_Model_Carrier_Savedquote::METHOD_STANDARD;

        $quote->getShippingAddress()
            ->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod($targetShippingCode);

        // Override shipping price and method name (only name)
        $rates = $quote->getShippingAddress()->collectShippingRates()
            ->getGroupedAllShippingRates();
        foreach ($rates as $carrier) {
            foreach ($carrier as $rate) {
                // Mage::log('checking '.$rate->getCode());
                if ($rate->getCode() == $targetShippingCode) {
                    // Apply saved shipping price and update name to original method
                    $rate->setCarrierTitle($savedQuote->getShippingMethod());
                    $rate->setPrice($savedQuote->getShippingCost());
                    $rate->save();
                }
            }
        }

        $quote->collectTotals();
        $quote->setIsActive(true);
        $quote->save();

        return $quote;
    }

    /**
     * Convert quote and and save it as a saved quote
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return SDM_SavedQuote_Model_SavedQuote|bool
     */
    public function saveQuote(Mage_Sales_Model_Quote $quote)
    {
        if ($quote->getSdmValutecGiftcardAmount()) {
            $quote->setSdmValutecGiftcard(null)
                ->setSdmValutecGiftcardAmount(null)
                ->setBaseSdmValutecGiftcardAmount(null)
                ->save()
                ->setTotalsCollectedFlag(false)
                ->collectTotals();
        }
        $address = $quote->getShippingAddress();
        $totals = $this->_getTotals($quote->getTotals());
        if (isset($totals['discount'])) {
            $subtotalWithDiscount = $totals['subtotal'] - abs($totals['discount']);
        } else {
            $subtotalWithDiscount = $totals['subtotal'];
        }

        $savedQuote = Mage::getModel('savedquote/savedquote')
            ->setQuoteId($quote->getId())
            ->setStoreId($this->getStoreId())
            ->setIsActive(SDM_SavedQuote_Helper_Data::PENDING_FLAG)
            ->setIncrementId()
            ->setName()
            ->setCustomerId($quote->getCustomerId())
            ->setCustomerTaxClassId($quote->getCustomerTaxClassId())
            ->setCustomerGroupId($quote->getCustomerGroupId())
            ->setCustomerEmail($quote->getCustomerEmail())
            ->setCustomerPrefix($quote->getCustomerPrefix())
            ->setCustomerFirstname($quote->getCustomerFirstname())
            ->setCustomerMiddlename($quote->getCustomerMiddlename())
            ->setCustomerLastname($quote->getCustomerLastname())
            ->setCustomerSuffix($quote->getCustomerSuffix())
            ->setCustomerNote()
            ->setCouponCodes($quote->getCouponCode())
            // ->setCarrier()   // Not available from sales_flat_quote_*
            // ->setCarrierTitle() // Not available from sales_flat_quote_*
            ->setShippingCode($address->getShippingMethod())
            ->setShippingMethod($address->getShippingDescription())
            ->setSubtotal($subtotalWithDiscount)
            ->setGrandTotal($totals['grand_total']);

        if ($address->getSdmShippingSurcharge() > 0) {
            $savedQuote->setSdmShippingSurcharge($address->getSdmShippingSurcharge());
        }

        if (!Mage::helper('sdm_preorder')->isQuotePreOrder($quote)) {
            if (isset($totals['tax'])) {
                $savedQuote->setTaxAmount($totals['tax']);
            }
            if (isset($totals['shipping'])) {
                $savedQuote->setShippingCost($totals['shipping']);
            }
        }
        // if (isset($totals['tax'])) {
        //     $savedQuote->setTaxAmount($totals['tax']);
        // }
        // if (isset($totals['shipping'])) {
        //     $savedQuote->setShippingCost($totals['shipping']);
        // }
        if (isset($totals['discount']) && abs($totals['discount']) > 0) {
            $savedQuote->setDiscount(abs($totals['discount']));
        }

        $savedQuote->save();

        return $savedQuote;
    }

    /**
     * Convert quote items and save them as saved quote items
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param int                    $parentId
     *
     * @return void
     */
    public function saveQuoteItems(Mage_Sales_Model_Quote $quote, $parentId)
    {
        $items = $quote->getAllVisibleItems();

        foreach ($items as $item) {
            $savedQuoteItem = Mage::getModel('savedquote/savedquote_item')
                ->setSavedQuoteId($parentId)
                ->setProductId($item->getProductId())
                ->setStoreId($this->getStoreId())
                ->setSku($item->getSku())
                ->setName($item->getName())
                ->setQty($item->getQty())
                ->setProductType($item->getProductType())
                // ->setItemOptions()   // no options needed for simple and grouped products
                ->setPrice($item->getPrice() - ($item->getDiscountAmount() / $item->getQty()))
                ->setTaxPercent($item->getTaxPercent())
                ->setTaxAmount($item->getTaxAmount())
                //->setSdmShippingSurcharge($item->getSdmShippingSurcharge())
                ->setRowTotal($item->getRowTotal() - $item->getDiscountAmount())
                ->setDiscountAmount($item->getDiscountAmount())
                ->setIsPreOrder($item->getIsPreOrder())
                ->setPreOrderReleaseDate($item->getPreOrderReleaseDate());

            $savedQuoteItem->save();
        }
    }

    /**
     * Convert quote shippign address and save them as saved quote shipping address
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param int                    $parentId
     *
     * @return void
     */
    public function saveQuoteShippingAddress(Mage_Sales_Model_Quote $quote, $parentId)
    {
        // Mage_Customer_Model_Address_Abstract::TYPE_BILLING

        $address = $quote->getShippingAddress();

        $savedQuoteAddress = Mage::getModel('savedquote/savedquote_address')
            ->setSavedQuoteId($parentId)
            ->setAddressType(Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING)
            ->setPrefix($address->getPrefix())
            ->setFirstname($address->getFirstname())
            ->setMiddlename($address->getMiddlename())
            ->setLastname($address->getLastname())
            ->setSuffix($address->getSuffix())
            ->setCompany($address->getCompany())
            // These are not updated with shipping total update from cart
            // ->setStreet(implode(PHP_EOL,$address->getStreet()))
            // ->setCity($address->getCity())
            ->setRegion($address->getRegion())
            ->setRegionId($address->getRegionId())
            ->setPostcode($address->getPostcode())
            ->setCountryId($address->getCountryId())
            ->setTelephone($address->getTelephone())
            ->setFax($address->getFax())
            ->setSameAsBilling(0);

        $savedQuoteAddress->save();
    }

    /**
     * Returns the required totals to save from the quote object
     *
     * @param array $totals Array Contains mixed objects
     *
     * @return array
     */
    protected function _getTotals($totals)
    {
        $values = array();

        foreach ($totals as $total) {
            $values[$total->getCode()] = $total->getValue();
        }

        return $values;
    }

    /**
     * Return the customer
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * Force remove every item from the current cart session
     *
     * @return $this
     */
    protected function _clearCurrentQuote()
    {
        return Mage::helper('savedquote')->clearCurrentQuote();
    }
}
