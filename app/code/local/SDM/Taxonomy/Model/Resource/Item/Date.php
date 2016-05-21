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
 * SDM_Taxonomy_Model_Resource_Item_Date class
 */
class SDM_Taxonomy_Model_Resource_Item_Date
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize table and PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('taxonomy/item_date', 'id');
    }

    /**
     * Get website ids
     *
     * @param integer $id
     *
     * @return array
     */
    public function getWebsiteIdsByTaxonomyId($id)
    {
        $websites = array();
        $result = $this->getDates($id, 'website_id');

        foreach ($result as $row) {
            $websites[] = $row['website_id'];
        }

        return $websites;
    }

    /**
     * Gets the taxonomy date data
     *
     * @param int       $tagId
     * @param array|str $columns
     * @param int       $websiteId
     *
     * @return array
     */
    public function getDates($tagId, $columns = '*', $websiteId = null)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from(
                array('main_table' => $this->getTable('taxonomy/item_date')),
                array() // select none and do it in ->columns()
            )
            ->where('main_table.taxonomy_id = ?', $tagId)
            ->columns($columns);

        if ($websiteId) {
            $select->where('main_table.website_id = ?', $websiteId);
        }

         $result = $adapter->fetchAll($select);

         return $result;
    }
}
