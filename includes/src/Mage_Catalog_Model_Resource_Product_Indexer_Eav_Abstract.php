<?php
/**
 * ELSN-451: Only _prepareRelationIndex has been rewritten.
 *
 * There is no other way to rewrite an abstract class.
 */

/**
 * Catalog Product Eav Attributes abstract indexer resource model
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Catalog_Model_Resource_Product_Indexer_Eav_Abstract
    extends Mage_Catalog_Model_Resource_Product_Indexer_Abstract
{
    /**
     * Rebuild all index data
     *
     *
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Eav_Abstract
     */
    public function reindexAll()
    {
        $this->useIdxTable(true);
        $this->beginTransaction();
        try {
            $this->clearTemporaryIndexTable();
            $this->_prepareIndex();
            $this->_prepareRelationIndex();
            $this->_removeNotVisibleEntityFromIndex();

            $this->syncData();
            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * Rebuild index data by entities
     *
     *
     * @param int|array $processIds
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Eav_Abstract
     * @throws Exception
     */
    public function reindexEntities($processIds)
    {
        $adapter = $this->_getWriteAdapter();

        $this->clearTemporaryIndexTable();

        if (!is_array($processIds)) {
            $processIds = array($processIds);
        }

        $parentIds = $this->getRelationsByChild($processIds);
        if ($parentIds) {
            $processIds = array_unique(array_merge($processIds, $parentIds));
        }
        $childIds  = $this->getRelationsByParent($processIds);
        if ($childIds) {
            $processIds = array_unique(array_merge($processIds, $childIds));
        }

        $this->_prepareIndex($processIds);
        $this->_prepareRelationIndex($processIds);
        $this->_removeNotVisibleEntityFromIndex();

        $adapter->beginTransaction();
        try {
            // remove old index
            $where = $adapter->quoteInto('entity_id IN(?)', $processIds);
            $adapter->delete($this->getMainTable(), $where);

            // insert new index
            $this->useDisableKeys(false);
            $this->insertFromTable($this->getIdxTable(), $this->getMainTable());
            $this->useDisableKeys(true);

            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * Rebuild index data by attribute id
     * If attribute is not indexable remove data by attribute
     *
     *
     * @param int $attributeId
     * @param bool $isIndexable
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Eav_Abstract
     */
    public function reindexAttribute($attributeId, $isIndexable = true)
    {
        if (!$isIndexable) {
            $this->_removeAttributeIndexData($attributeId);
        } else {
            $this->clearTemporaryIndexTable();

            $this->_prepareIndex(null, $attributeId);
            $this->_prepareRelationIndex();
            $this->_removeNotVisibleEntityFromIndex();

            $this->_synchronizeAttributeIndexData($attributeId);
        }

        return $this;
    }

    /**
     * Prepare data index for indexable attributes
     *
     * @param array $entityIds      the entity ids limitation
     * @param int $attributeId      the attribute id limitation
     */
    abstract protected function _prepareIndex($entityIds = null, $attributeId = null);

    /**
     * Remove Not Visible products from temporary data index
     *
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Eav_Abstract
     */
    protected function _removeNotVisibleEntityFromIndex()
    {
        $write      = $this->_getWriteAdapter();
        $idxTable   = $this->getIdxTable();

        $select = $write->select()
            ->from($idxTable, null);

        $condition = $write->quoteInto('=?',Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        $this->_addAttributeToSelect(
            $select,
            'visibility',
            $idxTable . '.entity_id',
            $idxTable . '.store_id',
            $condition
        );

        $query = $select->deleteFromSelect($idxTable);
        $write->query($query);

        return $this;
    }

    /**
     * Prepare data index for product relations
     *
     * @param array $parentIds  the parent entity ids limitation
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Eav_Abstract
     */
    protected function _prepareRelationIndex($parentIds = null)
    {
        /**
         * This method has been rewritten to prevent parent products from
         * inheriting child product attributes.
         */
        return $this;
    }

    /**
     * Retrieve condition for retrieve indexable attribute select
     * the catalog/eav_attribute table must have alias is ca
     *
     * @return string
     */
    protected function _getIndexableAttributesCondition()
    {
        $conditions = array(
            'ca.is_filterable_in_search > 0',
            'ca.is_visible_in_advanced_search > 0',
            'ca.is_filterable > 0'
        );

        return implode(' OR ', $conditions);
    }

    /**
     * Remove index data from index by attribute id
     *
     * @param int $attributeId
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Eav_Abstract
     */
    protected function _removeAttributeIndexData($attributeId)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();
        try {
            $where = $adapter->quoteInto('attribute_id = ?', $attributeId);
            $adapter->delete($this->getMainTable(), $where);
            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollback();
            throw $e;
        }

        return $this;
    }

    /**
     * Synchronize temporary index table with index table by attribute id
     *
     * @param int $attributeId
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Eav_Abstract
     * @throws Exception
     */
    protected function _synchronizeAttributeIndexData($attributeId)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();
        try {
            // remove index by attribute
            $where = $adapter->quoteInto('attribute_id = ?', $attributeId);
            $adapter->delete($this->getMainTable(), $where);

            // insert new index
            $this->insertFromTable($this->getIdxTable(), $this->getMainTable());

            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollback();
            throw $e;
        }

        return $this;
    }
}
