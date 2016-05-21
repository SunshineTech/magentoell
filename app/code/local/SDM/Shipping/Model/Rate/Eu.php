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
 * EU Rate Model
 */
class SDM_Shipping_Model_Rate_Eu extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'sdm_shipping_rate_eu';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'rate';

    /**
     * Initialize model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sdm_shipping/rate_eu');
    }
}
