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
 * SDM_Taxonomy_Model_Resource_Item class
 */
class SDM_Taxonomy_Model_Resource_Item
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize table and PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('taxonomy/item', 'entity_id');
    }

    /**
     * Process page data before saving
     *
     * @param  Mage_Core_Model_Abstract $object
     * @return SDM_Lpms_Model_Resource_Asset
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if ($object->isObjectNew() && !$object->hasCreationTime()) {
            $object->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
        }

        $object->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());

        return parent::_beforeSave($object);
    }

    /**
     * Loads associated date data and validate date range/website assignment
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return SDM_Taxonomy_Model_Item
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $websiteIds = array();

        // Set the dates
        $dates = Mage::getResourceModel('taxonomy/item_date')->getDates($object->getId());
        foreach ($dates as $date) {
            $object->setData("start_date_{$date['website_id']}", $date['start_date']);
            $object->setData("end_date_{$date['website_id']}", $date['end_date']);
            $websiteIds[] = (int)$date['website_id'];
        }
        $object->setWebsiteIds(implode(',', $websiteIds));

        return $object;
    }

    /**
     * Returns all of the option values for the given taxonomy item. The attribute
     * codes must be a concatenation of "tag_" and the taxonomy code.
     *
     * Note: This returns all assigned option values regardless of the products'
     * status.
     *
     * @param str $code
     *
     * @return array
     */
    public function getOptions($code)
    {
        $adapter = $this->_getReadAdapter();

        $attribute = Mage::getModel('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, "tag_$code");    // attribute code
        $attributeId = $attribute->getId();

        $select = $adapter->select()
            ->from(
                Mage::getSingleton('core/resource')
                    ->getTableName('catalog_product_entity_varchar'),
                'value'
            )
            ->where('attribute_id = ?', (int)$attributeId)
            ->where('store_id =?', '0')    // Indexable taxonomy is global
            ->where('`value` IS NOT NULL');

        $result = $adapter->fetchCol($select);

        if (empty($result)) {
            return array();
        }

        return array($attributeId => $result);
    }

    /**
     * Returns all of the date data
     *
     * @param array|str $columns
     * @param boolean   $getAssociatedData
     *
     * @return array
     */
    public function getAllData($columns = '*', $getAssociatedData = false)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from(
                array(
                    'main_table' => Mage::getSingleton('core/resource')
                        ->getTableName('taxonomy/item')
                ),
                array() // Select columns separately
            )
            ->columns($columns);

        if ($getAssociatedData) {
            $select->join(
                array('d' => $this->getTable('taxonomy/item_date')),
                'main_table.entity_id = d.taxonomy_id',
                array('*')
            );
        }

        $result = $adapter->fetchAll($select);

        if (empty($result)) {
            return array();
        }

        return $result;
    }

    /**
     * Returns the taxonomy record ID
     *
     * @param str $type
     * @param str $code
     *
     * @return int
     */
    public function getIdByCode($type, $code)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from($this->getTable('taxonomy/item'), 'entity_id')
            ->where('`code` = ?', (string)$code)
            ->where('`type` = ?', (string)$type);
        $result = $adapter->fetchCol($select);
        // Mage::log($select->__toString());
        if (!empty($result)) {
            return reset($result);
        }
    }
}
