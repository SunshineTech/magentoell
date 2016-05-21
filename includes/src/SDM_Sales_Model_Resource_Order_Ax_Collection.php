<?php
/**
 * Separation Degrees Media
 *
 * Ellison's Mage_Sales customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Sales
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Sales_Model_Resource_Order_Ax_Collection class
 */
class SDM_Sales_Model_Resource_Order_Ax_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sdm_sales/order_ax');
    }
}
