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
 * SDM_Lpms_Model_Resource_Asset class
 */
class SDM_Lpms_Model_Resource_Asset
    extends SDM_Lpms_Model_Resource_Abstract
{
    /**
     * Initialize table and PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('lpms/lpms_asset', 'entity_id');
        $this->_storeTableName = 'lpms/lpms_asset_store';
    }

    /**
     * Process store ids before deleting
     *
     * @param  Mage_Core_Model_Abstract $object
     * @return Mage_Lpms_Model_Resource_Asset
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        $assetImageIds = $this->allExistingAssetImageIdsByAssetId($object->getId());
        $this->deleteAssetImages($assetImageIds);

        return parent::_beforeDelete($object);
    }

    /**
     * Get's the entity_id from every asset assigned to a page
     *
     * @param  integer $pageId
     * @return SDM_Lpms_Model_Resource_Asset
     */
    public function allExistingAssetIdsByPage($pageId = null)
    {
        if (empty($pageId)) {
            return array();
        }

        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getTable('lpms/lpms_asset'), 'entity_id')
            ->where('cms_page_id = ?', (int)$pageId);

        return $adapter->fetchCol($select);
    }

    /**
     * Get's the entity_id from every asset assigned to a page
     *
     * @param  integer $pageId
     * @return SDM_Lpms_Model_Resource_Asset
     */
    public function allExistingAssetImageIdsByPage($pageId = null)
    {
        if (empty($pageId)) {
            return array();
        }

        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getTable('lpms/lpms_asset_image'), 'entity_id')
            ->where('cms_page_id = ?', (int)$pageId);

        return $adapter->fetchCol($select);
    }

    /**
     * Get's the entity_id from every asset assigned to a page
     *
     * @param  integer $assetId
     * @return SDM_Lpms_Model_Resource_Asset
     */
    public function allExistingAssetImageIdsByAssetId($assetId = null)
    {
        if (empty($assetId)) {
            return array();
        }

        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getTable('lpms/lpms_asset_image'), 'entity_id')
            ->where('cms_asset_id = ?', (int)$assetId);

        return $adapter->fetchCol($select);
    }
}
