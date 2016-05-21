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
 * Carrier for Saved Quotes
 */
class SDM_Shipping_Model_Carrier_Savedquote
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    const CARRIER_CODE    = 'sdm_shipping_savedquote';
    const METHOD_STANDARD = 'standard';

    /**
     * Carrier's code, as defined in parent class
     *
     * @var string
     */
    protected $_code = self::CARRIER_CODE;

    /**
     * Collect and get rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return Mage_Shipping_Model_Rate_Result|bool|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->isActive()) {
            return false;
        }
        /**
         * @var Mage_Shipping_Model_Rate_Result $result
         */
        $result = Mage::getModel('shipping/rate_result');

        $result->append($this->_getStandardRate());
        // More rate options can be appended as desired

        return $result;
    }

    /**
     * Returns Allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array(
            'standard' => 'Saved Quote'
        );
    }

    /**
     * Get Standard rate object
     *
     * @return Mage_Shipping_Model_Rate_Result_Method
     */
    protected function _getStandardRate()
    {
        /**
         * @var Mage_Shipping_Model_Rate_Result_Method $rate
         */
        $rate = Mage::getModel('shipping/rate_result_method');

        // Get the desired shipping amount from the saved quote database object
        $savedQuote = Mage::helper('savedquote')->getSavedQuote();
        $amount = empty($savedQuote) ? 0 : $savedQuote->getShippingCost();

        $rate->setCarrier(self::CARRIER_CODE);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod(self::METHOD_STANDARD);
        $rate->setMethodTitle('Standard');
        $rate->setPrice($amount);   // Set it high in case it accidentally appears on frontend
        $rate->setCost(0);

        return $rate;
    }

    /**
     * Determine whether current carrier enabled for activity
     *
     * @return boolean
     */
    public function isActive()
    {
        $active = $this->getConfigData('active');
        if ($active != 1 && $active != 'true') {
            return false;
        }
        if (!Mage::helper('savedquote')->isSavedQuoteSession()) {
            return false;
        }
        if (Mage::helper('sdm_preorder')->isQuotePreOrder()) {
            return false;
        }
        return true;
    }
}
