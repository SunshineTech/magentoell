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
 * SDM_CustomerDiscount_Model_Resource_Discountgroup class
 */
class SDM_CustomerDiscount_Model_Resource_Discountgroup extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('customerdiscount/discountgroup', 'id');
    }

    /**
     * Returns the record ID given the customer group code, which is also
     * the semantic name.
     *
     * @param str $code
     *
     * @return int
     */
    public function getIdByCode($code)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from(
                array('dc' => $this->getTable('customerdiscount/discountgroup')),
                'id'
                )
            ->join(
                array('t' => $this->getTable('taxonomy/item')),
                'code'
            )
            ->where('t.code = ?', (string)$code);

        $result = $adapter->fetchCol($select);

        if (!empty($result)) {
            return reset($result);
        }
    }
}
