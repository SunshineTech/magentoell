<?php
/**
 * Separation Degrees Media
 *
 * Ellison's custom Landing Page Management System (LPMS).
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Lpms
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Lpms_Model_Resource_Abstract class
 */
abstract class SDM_Lpms_Model_Resource_Abstract
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Process store ids before deleting
     *
     * @param  Mage_Core_Model_Abstract $object
     * @return Mage_Lpms_Model_Resource_Asset
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        $condition = array(
            'asset_id = ?'     => (int) $object->getId(),
        );
        $this->_getWriteAdapter()->delete($this->getTable($this->_storeTableName), $condition);

        return parent::_beforeDelete($object);
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
     * Assign page to store views
     *
     * @param  Mage_Core_Model_Abstract $object
     * @return SDM_Lpms_Model_Resource_Asset
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = $object->getStoreIds();
        $table  = $this->getTable($this->_storeTableName);
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = array(
                'asset_id = ?'     => (int) $object->getId(),
                'store_id IN (?)' => $delete
            );
            $this->_getWriteAdapter()->delete($table, $where);
        }

        if ($insert) {
            $data = array();
            foreach ($insert as $storeId) {
                $data[] = array(
                    'asset_id'  => (int) $object->getId(),
                    'store_id' => (int) $storeId
                );
            }
            $this->_getWriteAdapter()->insertMultiple($table, $data);
        }

        //Mark layout cache as invalidated
        Mage::app()->getCacheInstance()->invalidateType('layout');

        return parent::_afterSave($object);
    }

    /**
     * Perform operations after object load
     *
     * @param  Mage_Core_Model_Abstract $object
     * @return SDM_Lpms_Model_Resource_Asset
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $object->getStoreIds();

        return parent::_afterLoad($object);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param  int $entityId
     * @return array
     */
    public function lookupStoreIds($entityId)
    {
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getTable($this->_storeTableName), 'store_id')
            ->where('asset_id = ?', (int)$entityId);

        return $adapter->fetchCol($select);
    }

    /**
     * Deletes one or more assets
     * @param  array|int $assets
     * @return $this
     */
    public function deleteAssets($assets)
    {
        if (empty($assets)) {
            return $this;
        }
        if (!is_array($assets)) {
            $assets = array($assets);
        }
        foreach ($assets as $assetId) {
            $asset = Mage::getModel('lpms/asset')->load($assetId);
            if ($asset->getId()) {
                $asset->delete();
            }
        }
        return $this;
    }

    /**
     * Deletes one or more assets
     * @param  array|int $assetImages
     * @return $this
     */
    public function deleteAssetImages($assetImages)
    {
        if (empty($assetImages)) {
            return $this;
        }
        if (!is_array($assetImages)) {
            $assetImages = array($assetImages);
        }
        foreach ($assetImages as $assetImageId) {
            $assetImage = Mage::getModel('lpms/asset_image')->load($assetImageId);
            if ($assetImage->getId()) {
                $assetImage->delete();
            }
        }
        return $this;
    }

    /**
     * Deletes one or more assets
     * @param  array|int $assetIds
     * @return $this
     */
    public function deleteAssetImagesByAssetId($assetIds)
    {
        if (empty($assetIds)) {
            return $this;
        }
        if (!is_array($assetIds)) {
            $assetIds = array($assetIds);
        }
        $this->_getWriteAdapter()->delete(
            $this->getTable('lpms/lpms_asset'),
            array( 'entity_id IN (?)'     => $assetIds )
        );
        return $this;
    }
}
