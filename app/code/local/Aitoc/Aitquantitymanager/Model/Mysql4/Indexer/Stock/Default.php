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
    class Aitoc_Aitquantitymanager_Model_Mysql4_Indexer_Stock_Default extends Mage_CatalogInventory_Model_Mysql4_Indexer_Stock_Default
    {
        /**
         * Current Product Type Id
         *
         * @var string
         */
        protected $_typeId;

        /**
         * Product Type is composite flag
         *
         * @var bool
         */
        protected $_isComposite = false;

        /**
         * Initialize connection and define main table name
         *
         */
        protected function _construct()
        {
            $this->_init('aitquantitymanager/stock_status', 'product_id');
        }

        /**
         * Reindex all stock status data for default logic product type
         *
         * @return Mage_CatalogInventory_Model_Mysql4_Indexer_Stock_Default
         */
        public function reindexAll()
        {
            $this->_prepareIndexTable();
            return $this;
        }

        /**
         * Reindex stock data for defined product ids
         *
         * @param int|array $entityIds
         * @return Mage_CatalogInventory_Model_Mysql4_Indexer_Stock_Default
         */
        public function reindexEntity($entityIds)
        {
            $this->_updateIndex($entityIds);
            return $this;
        }

        /**
         * Set active Product Type Id
         *
         * @param string $typeId
         * @return Mage_CatalogInventory_Model_Mysql4_Indexer_Stock_Default
         */
        public function setTypeId($typeId)
        {
            $this->_typeId = $typeId;
            return $this;
        }

        /**
         * Retrieve active Product Type Id
         *
         * @throws Mage_Core_Exception
         * @return string
         */
        public function getTypeId()
        {
            if (is_null($this->_typeId)) {
                Mage::throwException(Mage::helper('cataloginventory')->__('Undefined product type'));
            }
            return $this->_typeId;
        }

        /**
         * Set Product Type Composite flag
         *
         * @param bool $flag
         * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Default
         */
        public function setIsComposite($flag)
        {
            $this->_isComposite = (bool)$flag;
            return $this;
        }

        /**
         * Check product type is composite
         *
         * @return bool
         */
        public function getIsComposite()
        {
            return $this->_isComposite;
        }

        /**
         * Retrieve is Global Manage Stock enabled
         *
         * @return bool
         */
        protected function _isManageStock()
        {
            return Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
        }

    //    protected $_aitquantitymanager_website_save_ids = array(); // aitoc code

        /**
         * Get the select object for get stock status by product ids
         *
         * @param int|array $entityIds
         * @param bool $usePrimaryTable use primary or temporary index table
         * @return Varien_Db_Select
         */
        protected function _getStockStatusSelect($entityIds = null, $usePrimaryTable = false)
        {
    // start aitoc code

            $adapter = $this->_getWriteAdapter();
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
    #                array('cisi' => $this->getTable('cataloginventory/stock_item')),
                    array('cisi' => $this->getTable('aitquantitymanager/stock_item')),
    //                'cisi.stock_id = cis.stock_id AND cisi.product_id = e.entity_id',
                    'cisi.stock_id = cis.stock_id AND cisi.product_id = e.entity_id AND cisi.product_id = e.entity_id AND cisi.website_id = pw.website_id', // aitoc code
    #                'cisi.stock_id = cis.stock_id AND cisi.product_id = e.entity_id AND cisi.website_id= ' . $iWebsiteId, // aitoc code
                    array())
                ->columns(array('qty' => new Zend_Db_Expr('IF(cisi.qty > 0, cisi.qty, 0)')))
                ->where('cw.website_id != 0')
    #            ->where('cw.website_id != ' . Mage::helper('aitquantitymanager')->getHiddenWebsiteId()) // aitoc code
    #            ->where('cw.website_id = ' . $iWebsiteId) // aitoc code
                ->where('e.type_id = ?', $this->getTypeId());

            // add limitation of status
            $condition = $adapter->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
            $this->_addAttributeToSelect($select, 'status', 'e.entity_id', 'cs.store_id', $condition);

            if ($this->_isManageStock()) {
                $statusExpr = new Zend_Db_Expr('IF(cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 0,'
                    . ' 1, cisi.is_in_stock)');
            } else {
                $statusExpr = new Zend_Db_Expr('IF(cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 1,'
                    . 'cisi.is_in_stock, 1)');
            }

            $select->columns(array('status' => $statusExpr));

            if (!is_null($entityIds)) {
                $select->where('e.entity_id IN(?)', $entityIds);
            }
    #d(get_ait_debug_print_backtrace(20), 1,1);

    # d($select->__tostring(),0);
            return $select;
        }

        /**
         * Prepare stock status data in temporary index table
         *
         * @param int|array $entityIds  the product limitation
         * @return Mage_CatalogInventory_Model_Mysql4_Indexer_Stock_Default
         */
        protected function _prepareIndexTable($entityIds = null)
        {
            $adapter = $this->_getWriteAdapter();
            $select  = $this->_getStockStatusSelect($entityIds);
            $query   = $select->insertFromSelect($this->getIdxTable());
            $adapter->query($query);

            return $this;
        }

        /**
         * Update Stock status index by product ids
         *
         * @param array|int $entityIds
         * @return Mage_CatalogInventory_Model_Mysql4_Indexer_Stock_Default
         */
        protected function _updateIndex($entityIds)
        {
            $adapter = $this->_getWriteAdapter();
            $select  = $this->_getStockStatusSelect($entityIds, true);
            $query   = $adapter->query($select);

            $i      = 0;
            $data   = array();
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $i ++;
                $data[] = array(
                    'product_id'    => $row['entity_id'],
                    'website_id'    => $row['website_id'],
                    'stock_id'      => $row['stock_id'],
                    'qty'           => $row['qty'],
                    'stock_status'  => (int)$row['status'],
                );
                if (($i % 1000) == 0) {
                    $this->_updateIndexTable($data);
                    $data = array();
                }
            }

            $this->_updateIndexTable($data);

            return $this;
        }

        /**
         * Update stock status index table (INSERT ... ON DUPLICATE KEY UPDATE ...)
         *
         * @param array $data
         * @return Mage_CatalogInventory_Model_Mysql4_Indexer_Stock_Default
         */
        protected function _updateIndexTable($data)
        {
            if (empty($data)) {
                return $this;
            }

            $adapter = $this->_getWriteAdapter();
            $adapter->insertOnDuplicate($this->getMainTable(), $data, array('qty', 'stock_status'));

            return $this;
        }
    }
}
elseif (version_compare(Mage::getVersion(), '1.4.1.0', 'ge'))
{
    class Aitoc_Aitquantitymanager_Model_Mysql4_Indexer_Stock_Default extends Mage_CatalogInventory_Model_Mysql4_Indexer_Stock_Default
    {
        /**
         * Initialize connection and define main table name
         *
         */
        protected function _construct()
        {
            $this->_init('aitquantitymanager/stock_status', 'product_id');
        }

        /**
         * Get the select object for get stock status by product ids
         *
         * @param int|array $entityIds
         * @param bool $usePrimaryTable use primary or temporary index table
         * @return Varien_Db_Select
         */
        protected function _getStockStatusSelect($entityIds = null, $usePrimaryTable = false)
        {
    // start aitoc code

            $adapter = $this->_getWriteAdapter();
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
    #                array('cisi' => $this->getTable('cataloginventory/stock_item')),
                    array('cisi' => $this->getTable('aitquantitymanager/stock_item')),
    //                'cisi.stock_id = cis.stock_id AND cisi.product_id = e.entity_id',
                    'cisi.stock_id = cis.stock_id AND cisi.product_id = e.entity_id AND cisi.product_id = e.entity_id AND cisi.website_id = pw.website_id', // aitoc code
    #                'cisi.stock_id = cis.stock_id AND cisi.product_id = e.entity_id AND cisi.website_id= ' . $iWebsiteId, // aitoc code
                    array())
                ->columns(array('qty' => new Zend_Db_Expr('IF(cisi.qty > 0, cisi.qty, 0)')))
                ->where('cw.website_id != 0')
    #            ->where('cw.website_id != ' . Mage::helper('aitquantitymanager')->getHiddenWebsiteId()) // aitoc code
    #            ->where('cw.website_id = ' . $iWebsiteId) // aitoc code
                ->where('e.type_id = ?', $this->getTypeId());

            // add limitation of status
            $condition = $adapter->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
            $this->_addAttributeToSelect($select, 'status', 'e.entity_id', 'cs.store_id', $condition);

            if ($this->_isManageStock()) {
                $statusExpr = new Zend_Db_Expr('IF(cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 0,'
                    . ' 1, cisi.is_in_stock)');
            } else {
                $statusExpr = new Zend_Db_Expr('IF(cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 1,'
                    . 'cisi.is_in_stock, 1)');
            }

            $select->columns(array('status' => $statusExpr));

            if (!is_null($entityIds)) {
                $select->where('e.entity_id IN(?)', $entityIds);
            }
    #d(get_ait_debug_print_backtrace(20), 1,1);

    # d($select->__tostring(),0);
            return $select;
        }
    }
}