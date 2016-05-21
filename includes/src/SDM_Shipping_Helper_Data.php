<?php
/**
 * Separation Degrees One
 *
 * Ellison and Sizzix Shipping Rules
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Shipping
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Helper class for SDM_Shipping
 */
class SDM_Shipping_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Calculate the surchage amount based on items in the card. The value returned
     * from this method is saved in the database. Thereore, null is returned if
     * surcharge amount is 0; $0 surcharge is equivalent to having no surcharge.
     *
     * @param boolean|Mage_Sales_Model_Quote $quote
     *
     * @return double|null
     */
    public function getSurchargeAmount($quote = false)
    {
        if (!Mage::getSingleton('sdm_shipping/config')->getIsSurchargeEnabled()) {
            return null;
        }
        $quote = $this->_getQuote($quote);
        if ($quote === false) {
            return null;
        }
        // If we have a surchage override active, then allow the current surchase to return
        // without looping through all visible items. Primarily used for saved quotes.
        if ($quote->getSdmShippingSurchargeOverride()) {
            return $quote->getSdmShippingSurchargeOverride();
        }
        // No override active. Calculate surcharge from quote items.
        $amount = 0;
        foreach ($quote->getAllVisibleItems() as $item) {
            $amount += $item->getSdmShippingSurcharge() * $item->getQty();
        }

        if ($amount > 0) {
            return $amount;
        }

        return null;
    }

    /**
     * Returns a third party tracking URL
     *
     * @param  Mage_Sales_Model_Order_Shipment_Track $track
     * @return string
     */
    public function getThirdPartyTrackingUrl($track)
    {
        $number = $track->getNumber();
        $title = preg_replace('/\s|-|_/', '', strtolower($track->getTitle()));

        // Check if USPS tracking number
        if (strpos($title, "usps") !== false) {
            return "https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=".$number;
        }

        // Check if FedEx tracking number
        if (strpos($title, "fedex") !== false) {
            return "https://www.fedex.com/apps/fedextrack/?action=track&trackingnumber=".$number;
        }

        return false;
    }

    /**
     * Validates the supplied quote or gets it from the session
     *
     * @param  boolean|Mage_Sales_Model_Quote $quote
     * @return boolean|Mage_Sales_Model_Quote
     */
    protected function _getQuote($quote)
    {
        if ($quote instanceof Mage_Sales_Model_Quote && $quote->getId()) {
            return $quote;
        }
        return Mage::getSingleton('checkout/session')->getQuote();
    }
}
