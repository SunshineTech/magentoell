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
class SDM_Shipping_Model_Carrier_Table_Eu
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = 'sdm_shipping_table_eu';

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
        $this->_request = $request;
        $this->_result = $this->_getQuotes();
        $this->_updateFreeMethodQuote($this->_request);
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
     * @return Mage_Shipping_Model_Rate_Result
     */
    protected function _getQuotes()
    {
        $price     = false;
        $total     = $this->_request->getBaseSubtotalInclTax();
        $tableRate = Mage::getModel('sdm_shipping/rate_eu')->getCollection()
            ->addFieldToFilter('country_id', $this->_request->getDestCountryId())
            ->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
            ->addFieldToFilter('min', array('lteq' => $total))
            ->addFieldToFilter('max', array('gteq' => $total))
            ->setOrder('rate', Zend_Db_Select::SQL_DESC)
            ->getFirstItem();
        if ($tableRate && $tableRate->getRate()) {
            $price = $tableRate->getRate();
        }
        if ($price === false) {
            return false;
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
        $rate->setCarrierTitle($this->getConfigData('carrier_title'));
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
