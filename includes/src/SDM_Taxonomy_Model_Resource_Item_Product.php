<?php
/**
 * Separation Degrees One
 *
 * Ellison's custom product taxonomy implementation.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Taxonomy
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Taxonomy_Model_Resource_Item_Product class
 */
class SDM_Taxonomy_Model_Resource_Item_Product
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize table and PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('taxonomy/item_product', 'id');
    }

    /**
     * Gets the taxonomy 'special' product data
     *
     * @param int       $tagId
     * @param array|str $columns
     * @param int       $productId
     *
     * @return array
     */
    public function getProducts($tagId, $columns = '*', $productId = null)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from(
                array('main_table' => $this->getTable('taxonomy/item_product')),
                array() // select none and do it in ->columns
            )
            ->where('main_table.taxonomy_id = ?', $tagId)
            ->columns($columns);

        if ($productId) {
            $select->where('main_table.product_id = ?', $productId);
        }

        $result = $adapter->fetchAll($select);

        return $result;
    }
}
