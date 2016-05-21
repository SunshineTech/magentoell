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
if (version_compare(Mage::getVersion(), '1.4.1.0', 'ge'))
{
    class Aitoc_Aitquantitymanager_Model_Mysql4_Stock extends Mage_CatalogInventory_Model_Mysql4_Stock
    {
        /**
         * Correct particular stock products qty based on operator
         *
         * @param Mage_CatalogInventory_Model_Stock $stock
         * @param array $productQtys array($productId => $qty)
         * @param string $operator +/-
         * @return Mage_CatalogInventory_Model_Mysql4_Stock
         */
        public function correctItemsQty($stock, $productQtys, $operator='-')
        {
            if (empty($productQtys)) {
                return $this;
            }

            // aitoc start
            $iWebsiteId = 0;
            $iStoreId   = 0;

            if ($iStoreId = Mage::registry('aitoc_order_refund_store_id')) // fix for refund
            {

            }
            else
            {
                $iStoreId = Mage::registry('aitoc_order_create_store_id'); // fix for create
            }

            if (!$iStoreId AND $controller = Mage::app()->getFrontController())
            {
                $oRequest = $controller->getRequest();
                if ($oRequest->getParam('website')) {
                    $storeIds = Mage::app()->getWebsite($oRequest->getParam('website'))->getStoreIds();
                    $iStoreId = array_pop($storeIds);
                } elseif ($oRequest->getParam('group')) {
                    $storeIds = Mage::app()->getGroup($oRequest->getParam('group'))->getStoreIds();
                    $iStoreId = array_pop($storeIds);
                } elseif ($oRequest->getParam('store')) {
                    $iStoreId = (int)$oRequest->getParam('store');
                } elseif ($oRequest->getParam('store_id')) {
                    $iStoreId = (int)$oRequest->getParam('store_id');
                } elseif (Mage::getSingleton('adminhtml/session_quote')->getStoreId()) {
                    $iStoreId = Mage::getSingleton('adminhtml/session_quote')->getStoreId();
                } else {
                    $iStoreId = Mage::app()->getStore()->getId();
                }
            }

            if (!$iWebsiteId AND $iStoreId)
            {
                $store = Mage::app()->getStore($iStoreId);

    #            Mage::app()->getStores(true, true);

                $iWebsiteId = $store->getWebsiteId();
            }

            if (!$iWebsiteId)
            {
                $iWebsiteId = Mage::helper('aitquantitymanager')->getHiddenWebsiteId(); // default
            }

            // aitoc end


            // aitoc modifications start
            // will update one by one, as we need to check website
            foreach ($productQtys as $productId => $qty)
            {
                $websiteCond = array($iWebsiteId);
                $websiteSelect = $this->_getReadAdapter()->select()
                                                         ->from($this->getTable('cataloginventory/stock_item'), 'website_id')
                                                         ->where('product_id = ?',                $productId)
                                                         ->where('stock_id = ?',                  $stock->getId())
                                                         ->where('website_id = ?',                $iWebsiteId)
                                                         ->where('use_default_website_stock = ?', 0);
                $fetchedWebsiteId = $this->_getConnection('read')->fetchOne($websiteSelect);
                if ($fetchedWebsiteId != $iWebsiteId)
                {
                    // adding default if stock is set to 'use defaults' for current website
                    $websiteCond[] = Mage::helper('aitquantitymanager')->getHiddenWebsiteId();
                    
                    // adding all default-dependent websites
                    $websiteSelect = $this->_getReadAdapter()->select()
                                                         ->from($this->getTable('cataloginventory/stock_item'), 'website_id')
                                                         ->where('product_id = ?',                $productId)
                                                         ->where('stock_id = ?',                  $stock->getId())
                                                         ->where('use_default_website_stock = ?', 1);
                    $dependentWebsites = $this->_getConnection('read')->fetchAll($websiteSelect);
                    foreach($dependentWebsites as $website)
                    {
                        if((!in_array($website['website_id'],$websiteCond)))
                        {
                            $websiteCond[]=$website['website_id'];
                        }
                    }
                }

                $query = 'UPDATE '.$this->getTable('cataloginventory/stock_item').' SET ';
                $query.= $this->_getWriteAdapter()->quoteInto(' `qty` = `qty`'.$operator.'? ', $qty);
                $query.= $this->_getWriteAdapter()->quoteInto(' WHERE `product_id` =?', $productId);
                $query.= $this->_getWriteAdapter()->quoteInto(' AND `stock_id` =?', $stock->getId());
                $query.= $this->_getWriteAdapter()->quoteInto(' AND `website_id` IN (?)', $websiteCond);

                $this->_getWriteAdapter()->beginTransaction();
                $this->_getWriteAdapter()->query($query);
                $this->_getWriteAdapter()->commit();
            }
            // aitoc modifications end
            return $this;
        }

        /**
         * Get stock items data for requested products
         *
         * @param Mage_CatalogInventory_Model_Stock $stock
         * @param array $productIds
         * @param bool $lockRows
         */
        public function getProductsStock($stock, $productIds, $lockRows = false)
        {
            // aitoc start
            $iWebsiteId = 0;
            $iStoreId   = 0;

            if ($iStoreId = Mage::registry('aitoc_order_refund_store_id')) // fix for refund
            {

            }
            else
            {
                $iStoreId = Mage::registry('aitoc_order_create_store_id'); // fix for create
            }

            if (!$iStoreId AND $controller = Mage::app()->getFrontController())
            {
                $oRequest = $controller->getRequest();
                if ($oRequest->getParam('website')) {
                    $storeIds = Mage::app()->getWebsite($oRequest->getParam('website'))->getStoreIds();
                    $iStoreId = array_pop($storeIds);
                } elseif ($oRequest->getParam('group')) {
                    $storeIds = Mage::app()->getGroup($oRequest->getParam('group'))->getStoreIds();
                    $iStoreId = array_pop($storeIds);
                } elseif ($oRequest->getParam('store')) {
                    $iStoreId = (int)$oRequest->getParam('store');
                } elseif ($oRequest->getParam('store_id')) {
                    $iStoreId = (int)$oRequest->getParam('store_id');
                } elseif (Mage::getSingleton('adminhtml/session_quote')->getStoreId()) {
                    $iStoreId = Mage::getSingleton('adminhtml/session_quote')->getStoreId();
                } else {
                    $iStoreId = Mage::app()->getStore()->getId();
                }
            }

            if (!$iWebsiteId AND $iStoreId)
            {
                $store = Mage::app()->getStore($iStoreId);
                $iWebsiteId = $store->getWebsiteId();
            }

            if (!$iWebsiteId)
            {
                $iWebsiteId = Mage::helper('aitquantitymanager')->getHiddenWebsiteId(); // default
            }
            // aitoc end

            $itemTable = $this->getTable('cataloginventory/stock_item');
            $select = $this->_getWriteAdapter()->select();

            if (version_compare(Mage::getVersion(), '1.4.1.1', 'lt'))
            {
                $select
                    ->from($itemTable);
            }
            else
            {
                $productTable = $this->getTable('catalog/product');
                $select
                    ->from(array('si' => $itemTable))
                    ->join(array('p' => $productTable), 'p.entity_id=si.product_id', array('type_id'));
            }

            $select
                ->where('stock_id=?', $stock->getId())
                ->where('product_id IN(?)', $productIds)
                // aitoc start
                ->where('website_id=?', $iWebsiteId)
                // aitoc end
                ->forUpdate($lockRows);
            return $this->_getWriteAdapter()->fetchAll($select);
        }
    }
}
else
{
    class Aitoc_Aitquantitymanager_Model_Mysql4_Stock extends Mage_CatalogInventory_Model_Mysql4_Stock
    {
    }
}