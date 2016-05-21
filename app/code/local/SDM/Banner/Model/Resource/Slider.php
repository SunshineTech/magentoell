<?php
/**
 * Separation Degrees One
 *
 * Banner Ads
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Banner
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */
/**
 * SDM_Banner_Model_Resource_Slider class
 */
class SDM_Banner_Model_Resource_Slider
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize
     *
     * @return void
     */
    public function _construct()
    {
        // Note that the slider_id refers to the key field in your database table.
        $this->_init('slider/slider', 'slider_id');
    }

    /**
     * After delete logic
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return SDM_Banner_Model_Resource_Slider
     */
    protected function _afterDelete(Mage_Core_Model_Abstract $object)
    {
        $condition = $this->_getWriteAdapter()->quoteInto('slider_id = ?', $object->getId());

        // Stores
        $this->_getWriteAdapter()->delete($this->getTable('slider/stores'), $condition);

        // Pages
        $this->_getWriteAdapter()->delete($this->getTable('slider/pages'), $condition);

        return parent::_afterDelete($object);
    }

    /**
     * After save logic
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return SDM_Banner_Model_Resource_Slider
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $condition = $this->_getWriteAdapter()->quoteInto('slider_id = ?', $object->getId());

        // Stores
        $this->_getWriteAdapter()->delete($this->getTable('slider/stores'), $condition);
        foreach ((array)$object->getData('stores') as $store) {
            $storeArray = array();
            $storeArray['slider_id'] = $object->getId();
            $storeArray['store_id'] = $store;
            $this->_getWriteAdapter()->insert($this->getTable('slider/stores'), $storeArray);
        }

        // Pages
        $this->_getWriteAdapter()->delete($this->getTable('slider/pages'), $condition);
        foreach ((array)$object->getData('pages') as $page) {
            $pageArray = array();
            $pageArray['slider_id'] = $object->getId();
            $pageArray['layout_id'] = $page;
            $this->_getWriteAdapter()->insert($this->getTable('slider/pages'), $pageArray);
        }

        return parent::_afterSave($object);
    }

    /**
     * Load the model
     *
     * @param Mage_Core_Model_Abstract $object
     * @param string                   $value
     * @param string|null              $field
     *
     * @return SDM_Banner_Model_Resource_Slider
     */
    public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {

        if (!intval($value) && is_string($value)) {
            $field = 'slider_id';
        }
        return parent::load($object, $value, $field);
    }

    /**
     * After load logic
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return SDM_Banner_Model_Resource_Slider
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId()) {
            // Stores
            $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('slider/stores'))
                ->where('slider_id = ?', $object->getId());
            if ($data = $this->_getReadAdapter()->fetchAll($select)) {
                $storesArray = array();
                foreach ($data as $row) {
                    $storesArray[] = $row['store_id'];
                }
                $object->setData('stores', $storesArray);
            }

            // Pages
            $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('slider/pages'))
                ->where('slider_id = ?', $object->getId());
            if ($data = $this->_getReadAdapter()->fetchAll($select)) {
                $pagesArray = array();
                foreach ($data as $row) {
                    $pagesArray[] = $row['layout_id'];
                }
                $object->setData('pages', $pagesArray);
            }
        }
        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string        $field
     * @param mixed         $value
     * @param Varien_Object $object
     *
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {

        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $select
                ->join(
                    array('wcs' => $this->getTable('slider/stores')),
                    $this->getMainTable().'.slider_id = wcs.slider_id'
                )
                ->where('wcs.store_id in (0, ?) ', $object->getStoreId())
                ->order('store_id DESC')
                ->limit(1);
        }
        return $select;
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param  int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        return $this->_getReadAdapter()->fetchCol(
            $this->_getReadAdapter()->select()
                ->from($this->getTable('slider/stores'), 'store_id')
                ->where("{$this->getIdFieldName()} = ?", $id)
        );
    }
}
