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

class Aitoc_Aitquantitymanager_Model_Rewrite_FrontBundleMysql4IndexerStock extends Mage_Bundle_Model_Mysql4_Indexer_Stock
{
    protected $_aitquantitymanager_website_save_ids = array(); // aitoc code
    
    // overide parent
    protected function _prepareBundleOptionStockData($entityIds = null, $usePrimaryTable = false)
    {
        $this->_cleanBundleOptionStockData();
        $idxTable = $usePrimaryTable ? $this->getMainTable() : $this->getIdxTable();
        $adapter  = $this->_getWriteAdapter();
        $select   = $adapter->select()
            ->from(array('bo' => $this->getTable('bundle/option')), array('parent_id'));
        $this->_addWebsiteJoinToSelect($select, false);
        $select->columns('website_id', 'cw')
            ->join(
#                array('cis' => $this->getTable('cataloginventory/stock')),
                array('cis' => Mage::helper('aitquantitymanager')->getCataloginventoryStockTable()), // aitoc code
                '',
                array('stock_id'))
            ->joinLeft(
                array('bs' => $this->getTable('bundle/selection')),
                'bs.option_id = bo.option_id',
                array())
            ->joinLeft(
                array('i' => $idxTable),
                'i.product_id = bs.product_id AND i.website_id = cw.website_id AND i.stock_id = cis.stock_id',
                array())
            ->joinLeft(
                array('e' => $this->getTable('catalog/product')),
                'e.entity_id = bs.product_id',
                array())
            ->where('cw.website_id != 0')
            ->group(array('bo.parent_id', 'cw.website_id', 'cis.stock_id', 'bo.option_id'))
            ->columns(array(
                'option_id' => 'bo.option_id',
                'status'    => new Zend_Db_Expr("MAX(IF(e.required_options = 0, i.stock_status, 0))")
            ));

        if (!is_null($entityIds)) {
            $select->where('bo.parent_id IN(?)', $entityIds);
        }

        // clone select for bundle product without required bundle options
        $selectNonRequired = clone $select;

        $select->where('bo.required = ?', 1);
        $selectNonRequired->where('bo.required = ?', 0)
            ->having('`status` = 1');

        $query = $select->insertFromSelect($this->_getBundleOptionTable());
        $adapter->query($query);

        $query = $selectNonRequired->insertFromSelect($this->_getBundleOptionTable());
        $adapter->query($query);

        return $this;
    }

    // overide parent
    protected function _getStockStatusSelect($entityIds = null, $usePrimaryTable = false)
    {
        $this->_prepareBundleOptionStockData($entityIds, $usePrimaryTable);

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
                array('cisi' => $this->getTable('aitquantitymanager/stock_item')), // aitoc code
//                'cisi.stock_id = cis.stock_id AND cisi.product_id = e.entity_id',
                'cisi.stock_id = cis.stock_id AND cisi.product_id = e.entity_id AND cisi.product_id = e.entity_id AND cisi.website_id = pw.website_id', // aitoc code
                array())
            ->joinLeft(
                array('o' => $this->_getBundleOptionTable()),
                'o.entity_id = e.entity_id AND o.website_id = cw.website_id AND o.stock_id = cis.stock_id',
                array())
            ->columns(array('qty' => new Zend_Db_Expr('0')))
            ->where('cw.website_id != 0')
            ->where('e.type_id = ?', $this->getTypeId())
            ->group(array('e.entity_id', 'cw.website_id', 'cis.stock_id'));

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

        $select->columns(array('status' => new Zend_Db_Expr("LEAST(MIN(o.stock_status), {$statusExpr})")));

        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        return $select;
    }
    
    
}