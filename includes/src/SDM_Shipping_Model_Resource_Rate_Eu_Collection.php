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
 * EU rate resource collection model
 */
class SDM_Shipping_Model_Resource_Rate_Eu_Collection
     extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sdm_shipping/rate_eu');
    }
}
