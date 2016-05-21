<?php
/**
 * Separation Degrees One
 *
 * Implements the customer/retailer discount logic for viewing and obtaining
 * discounts and prices, as wel as managing the customer groups.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CustomerDiscount
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_CustomerDiscount_Model_Resource_Discountgroup_Collection class
 */
class SDM_CustomerDiscount_Model_Resource_Discountgroup_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('customerdiscount/discountgroup');
    }
}
