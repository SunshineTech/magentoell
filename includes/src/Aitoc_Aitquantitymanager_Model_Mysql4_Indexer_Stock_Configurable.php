<?php
/**
 * Multi-Location Inventory
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitquantitymanager
 * @version      2.2.3
 * @license:     rJhV4acfvLy4sPgpe7MoLJnfOEhDVfWVuKRvbpcv30
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
if (version_compare( Mage::getVersion(), '1.4.0.0', 'ge') && version_compare( Mage::getVersion(), '1.4.1.0', 'lt'))
{
    class Aitoc_Aitquantitymanager_Model_Mysql4_Indexer_Stock_Configurable extends Mage_CatalogInventory_Model_Mysql4_Indexer_Stock_Configurable
    {
        /**
         * Reindex all stock status data for configurable products
         *
         * @return Mage_CatalogInventory_Model_Mysql4_Indexer_Stock_Configurable
         */
        public function reindexAll()
        {
            $this->_prepareIndexTable();
            return $this;
        }
    
        /**
         * Reindex stock data for defined configurable product ids
         *
         * @param int|array $entityIds
         * @return Mage_CatalogInventory_Model_Mysql4_Indexer_Stock_Configurable
         */
        public function reindexEntity($entityIds)
        {
            $this->_updateIndex($entityIds);
            return $this;
        }
    
        protected $_aitquantitymanager_website_save_ids = array(); // aitoc code
        
        /**
         * Get the select object for get stock status by product ids
         *
         * @param int|array $entityIds
         * @param bool $usePrimaryTable use primary or temporary index table
         * @return Varien_Db_Select
         */
        protected function _getStockStatusSelect($entityIds = null, $usePrimaryTable = false)
        {
            $adapter  = $this->_getWriteAdapter();
            $idxTable = $usePrimaryTable ? $this->getMainTable() : $this->getIdxTable();
            $select  = $adapter->select()
                ->from(array('e' => $this->getTable('catalog/product')), array('entity_id'));
            $this->_addWebsiteJoinToSelect($select, true);
            $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', 'e.entity_id');
            $select->columns('cw.website_id')
                ->join(
    #                array('cis' => $this->getTable('cataloginventory/stock')),
                    array('cis' => Mage::helper('aitquantitymanager')->getCataloginventoryStockTable()), // aitoc code
                    '',
                    array('stock_id'))
                ->joinLeft(
    //                array('cisi' => $this->getTable('cataloginventory/stock_item')),
                    array('cisi' => $this->getTable('aitquantitymanager/stock_item')),
    //                'cisi.stock_id = cis.stock_id AND cisi.product_id = e.entity_id',
                    'cisi.stock_id = cis.stock_id AND cisi.product_id = e.entity_id AND cisi.product_id = e.entity_id AND cisi.website_id = pw.website_id', // aitoc code
                    array())
                ->joinLeft(
                    array('l' => $this->getTable('catalog/product_super_link')),
                    'l.parent_id = e.entity_id',
                    array())
                ->join(
                    array('le' => $this->getTable('catalog/product')),
                    'le.entity_id = l.product_id',
                    array())
                ->joinLeft(
                    array('i' => $idxTable),
                    'i.product_id = l.product_id AND cw.website_id = i.website_id AND cis.stock_id = i.stock_id',
                    array())
                ->columns(array('qty' => new Zend_Db_Expr('0')))
                ->where('cw.website_id != 0')
    #            ->where('cw.website_id IN (' . implode(',', $aWebsiteSaveIds) . ')') // aitoc code
                ->where('e.type_id = ?', $this->getTypeId())
                ->group(array('e.entity_id', 'cw.website_id', 'cis.stock_id'));
    
            $psExpr = $this->_addAttributeToSelect($select, 'status', 'e.entity_id', 'cs.store_id');
            $psCond = $adapter->quoteInto($psExpr . '=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
    
            if ($this->_isManageStock()) {
                $statusExpr = new Zend_Db_Expr('IF(cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 0,'
                    . ' 1, cisi.is_in_stock)');
            } else {
                $statusExpr = new Zend_Db_Expr('IF(cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 1,'
                    . 'cisi.is_in_stock, 1)');
            }
    
            $stockStatusExpr = new Zend_Db_Expr("LEAST(MAX(IF({$psCond} AND le.required_options = 0, i.stock_status, 0))"
                . ", {$statusExpr})");
    
            $select->columns(array(
                'status' => $stockStatusExpr
            ));
    
            if (!is_null($entityIds)) {
                $select->where('e.entity_id IN(?)', $entityIds);
            }
    
            return $select;
        }
    }
}
elseif (version_compare(Mage::getVersion(), '1.4.1.0', 'ge'))
{
    class Aitoc_Aitquantitymanager_Model_Mysql4_Indexer_Stock_Configurable extends Mage_CatalogInventory_Model_Mysql4_Indexer_Stock_Configurable
    {
        protected $_aitquantitymanager_website_save_ids = array(); // aitoc code

        /**
         * Get the select object for get stock status by product ids
         *
         * @param int|array $entityIds
         * @param bool $usePrimaryTable use primary or temporary index table
         * @return Varien_Db_Select
         */
        protected function _getStockStatusSelect($entityIds = null, $usePrimaryTable = false)
        {
            $adapter  = $this->_getWriteAdapter();
            $idxTable = $usePrimaryTable ? $this->getMainTable() : $this->getIdxTable();
            $select  = $adapter->select()
                ->from(array('e' => $this->getTable('catalog/product')), array('entity_id'));
            $this->_addWebsiteJoinToSelect($select, true);
            $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', 'e.entity_id');
            $select->columns('cw.website_id')
                ->join(
    #                array('cis' => $this->getTable('cataloginventory/stock')),
                    array('cis' => Mage::helper('aitquantitymanager')->getCataloginventoryStockTable()), // aitoc code
                    '',
                    array('stock_id'))
                ->joinLeft(
    //                array('cisi' => $this->getTable('cataloginventory/stock_item')),
                    array('cisi' => $this->getTable('aitquantitymanager/stock_item')),
    //                'cisi.stock_id = cis.stock_id AND cisi.product_id = e.entity_id',
                    'cisi.stock_id = cis.stock_id AND cisi.product_id = e.entity_id AND cisi.product_id = e.entity_id AND cisi.website_id = pw.website_id', // aitoc code
                    array())
                ->joinLeft(
                    array('l' => $this->getTable('catalog/product_super_link')),
                    'l.parent_id = e.entity_id',
                    array())
                ->join(
                    array('le' => $this->getTable('catalog/product')),
                    'le.entity_id = l.product_id',
                    array())
                ->joinLeft(
                    array('i' => $idxTable),
                    'i.product_id = l.product_id AND cw.website_id = i.website_id AND cis.stock_id = i.stock_id',
                    array())
                ->columns(array('qty' => new Zend_Db_Expr('0')))
                ->where('cw.website_id != 0')
    #            ->where('cw.website_id IN (' . implode(',', $aWebsiteSaveIds) . ')') // aitoc code
                ->where('e.type_id = ?', $this->getTypeId())
                ->group(array('e.entity_id', 'cw.website_id', 'cis.stock_id'));

            $psExpr = $this->_addAttributeToSelect($select, 'status', 'e.entity_id', 'cs.store_id');
            $psCond = $adapter->quoteInto($psExpr . '=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

            if ($this->_isManageStock()) {
                $statusExpr = new Zend_Db_Expr('IF(cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 0,'
                    . ' 1, cisi.is_in_stock)');
            } else {
                $statusExpr = new Zend_Db_Expr('IF(cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 1,'
                    . 'cisi.is_in_stock, 1)');
            }

            $stockStatusExpr = new Zend_Db_Expr("LEAST(MAX(IF({$psCond} AND le.required_options = 0, i.stock_status, 0))"
                . ", {$statusExpr})");

            $select->columns(array(
                'status' => $stockStatusExpr
            ));

            if (!is_null($entityIds)) {
                $select->where('e.entity_id IN(?)', $entityIds);
            }

            return $select;
        }
    }
}