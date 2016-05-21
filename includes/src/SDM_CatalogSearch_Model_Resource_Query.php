<?php
/**
 * Separation Degrees One
 *
 * Magento catalog search customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CatalogSearch
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_CatalogSearch_Model_Resource_Query class
 */
class SDM_CatalogSearch_Model_Resource_Query extends Mage_CatalogSearch_Model_Resource_Query
{
    /**
     * Custom load model by search query string
     *
     * @param  Mage_Core_Model_Abstract $object
     * @param  string                   $value
     * @return Mage_CatalogSearch_Model_Resource_Query
     */
    public function loadByQuery(Mage_Core_Model_Abstract $object, $value)
    {
        $readAdapter = $this->_getReadAdapter();
        $select = $readAdapter->select();

        $synonymSelect = clone $select;
        $synonymSelect
            ->from($this->getMainTable())
            ->where('store_id = ?', $object->getStoreId());

        $querySelect = clone $synonymSelect;
        $querySelect->where('query_text = ?', $value);
        
        $querySelect->where('type = ?', Mage::helper('sdm_catalog')->getCatalogType());

        $synonymSelect->where('synonym_for = ?', $value);

        $select->union(array($querySelect, "($synonymSelect)"), Zend_Db_Select::SQL_UNION_ALL)
            ->order('synonym_for ASC')
            ->limit(1);

        $data = $readAdapter->fetchRow($select);
        if ($data) {
            $object->setData($data);
            $this->_afterLoad($object);
        }

        return $this;
    }

    /**
     * Custom load model only by query text (skip synonym for)
     *
     * @param  Mage_Core_Model_Abstract $object
     * @param  string                   $value
     * @return Mage_CatalogSearch_Model_Resource_Query
     */
    public function loadByQueryText(Mage_Core_Model_Abstract $object, $value)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where('query_text = ?', $value)
            ->where('store_id = ?', $object->getStoreId())
            ->where('type = ?', Mage::helper('sdm_catalog')->getCatalogType())
            ->limit(1);
        if ($data = $this->_getReadAdapter()->fetchRow($select)) {
            $object->setData($data);
            $this->_afterLoad($object);
        }
        return $this;
    }

    /**
     * Enter description here ... ¯\_(ツ)_/¯
     *
     * @param  Mage_Core_Model_Abstract $object
     * @return Mage_CatalogSearch_Model_Resource_Query
     */
    public function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $object->setType(Mage::helper('sdm_catalog')->getCatalogType());
        return parent::_beforeSave($object);
    }
}
