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
 * Carrier for US table rates
 */
class SDM_Shipping_Model_Carrier_Table_Us_Noncontinental
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = 'sdm_shipping_table_us_noncontinental';

    /**
     * Rate request data
     *
     * @var Mage_Shipping_Model_Rate_Request|null
     */
    protected $_request = null;

    /**
     * Rate result data
     *
     * @var Mage_Shipping_Model_Rate_Result|null
     */
    protected $_result = null;

    /**
     * Gather shipping rates
     *
     * @param  Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result|boolean
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if ($request->getDestCountryId() != SDM_Shipping_Model_Carrier_Table_Us::COUNTRY_ID_US) {
            return false;
        }
        $this->_request = $request;
        $this->_result = $this->_getQuotes();
        if ($this->_result) {
            $this->_updateFreeMethodQuote($this->_request);
        }
        return $this->_result;
    }

    /**
     * Returns Allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array(
            'standard' => $this->getConfigData('method_title')
        );
    }

    /**
     * Get table rates
     *
     * @return Mage_Shipping_Model_Rate_Result|boolean
     */
    protected function _getQuotes()
    {
        $suffix = false;
        if (in_array($this->_request->getDestRegionCode(), explode(',', $this->getConfigData('state')))) {
            $suffix = '';
        }
        if (in_array($this->_request->getDestRegionCode(), explode(',', $this->getConfigData('state_military')))) {
            $suffix = '_military';
        }
        if ($suffix === false) {
            return false;
        }
        $total = $this->_request->getBaseSubtotalInclTax();
        if ($total < $this->getConfigData('low_tier_cutoff' . $suffix)) {
            $price = $this->getConfigData('low_tier_price' . $suffix);
        } else {
            $price = $total * $this->getConfigData('high_tier_price_modifier' . $suffix);
        }
        /**
         * @var Mage_Shipping_Model_Rate_Result
         */
        $result = Mage::getModel('shipping/rate_result');
        /**
         * @var $rate Mage_Shipping_Model_Rate_Result_Method
         */
        $rate = Mage::getModel('shipping/rate_result_method');
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('standard');
        $rate->setMethodTitle($this->getConfigData('method_title'));
        $rate->setPrice($price);
        $result->append($rate);
        return $result;
    }

    /**
     * Set free method request
     *
     * @param string $freeMethod
     *
     * @return null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _setFreeMethodRequest($freeMethod)
    {
        // ¯\_(ツ)_/¯
    }
}
