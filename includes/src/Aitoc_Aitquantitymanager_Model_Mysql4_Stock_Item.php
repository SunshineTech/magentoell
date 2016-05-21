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
    class Aitoc_Aitquantitymanager_Model_Mysql4_Stock_Item extends Mage_Core_Model_Mysql4_Abstract
    {
        protected function  _construct()
        {
    #        $this->_init('cataloginventory/stock_item', 'item_id');
            $this->_init('aitquantitymanager/stock_item', 'item_id');

        }

    // start aitoc

        public function load(Mage_Core_Model_Abstract $object, $value, $field=null)
        {
            if (is_null($field)) {
                $field = $this->getIdFieldName();
            }

            $read = $this->_getReadAdapter();
            if ($read && !is_null($value)) {
                $select = $this->_getLoadSelect($field, $value, $object);
                $data = $read->fetchRow($select);

                if ($data) {
                    $object->setData($data);
                }
            }

            $this->_afterLoad($object);

            return $this;
        }


        public function getProductItemHash($iProductId, $stockId = 1)
        {
            $aItemHash = array();

            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable(), array('item_id', 'website_id', 'use_default_website_stock'))
                ->where('product_id =?', $iProductId)
                ->where('stock_id=?', $stockId)
                ->where('website_id <> 0');

            $aItemList = $this->_getReadAdapter()->fetchAll($select);

            if ($aItemList)
            {
                foreach ($aItemList as $aItem)
                {
                    $aItemHash[$aItem['website_id']] = $aItem;
                }
            }

            return $aItemHash;
        }

        public function getProductDefaultItem($iProductId, $stockId = 1)
        {

            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable(), '*')
                ->where('product_id =?', $iProductId)
                ->where('stock_id=?', $stockId)
                ->where('website_id=?', Mage::helper('aitquantitymanager')->getHiddenWebsiteId());
            return $this->_getReadAdapter()->fetchRow($select);
        }


    //fin aitoc


        /**
         * Loading stock item data by product
         *
         * @param   Mage_CatalogInventory_Model_Stock_Item $item
         * @param   int $productId
         * @return  Mage_Core_Model_Mysql4_Abstract
         */
        public function loadByProductId(Mage_CatalogInventory_Model_Stock_Item $item, $productId, $websiteId = null)
        {
            if (!$websiteId)
            {
                if ($iStoreId = Mage::registry('aitoc_order_refund_store_id')) // fix for refund
                {
                    $store = Mage::app()->getStore($iStoreId);
        #            d($store);
                    $websiteId = $store->getWebsiteId();
                }
                else
                {
                    $websiteId = Mage::app()->getStore()->getWebsiteId();
                }

                if (!$websiteId)
                {
                    $websiteId = 1;
                }
    #            d($websiteId, 1);
            }

            $select = $this->_getLoadSelect('product_id', $productId, $item)
                ->where('stock_id=?', $item->getStockId())
                ->where('website_id=?', $websiteId);
    #$s = $select->__toString();

            $item->setData($this->_getReadAdapter()->fetchRow($select));
            $this->_afterLoad($item);
    #        d($websiteId, 1);
            return $this;
        }

    // srart atioc
        public function getDataByProductId($iProductId, $iWebsiteId, $iStockId = 1)
        {
            $select = $this->_getLoadSelect('product_id', $iProductId, $this)
                ->where('stock_id=?', $iStockId)
                ->where('website_id=?', $iWebsiteId);

            return $this->_getReadAdapter()->fetchRow($select);
        }

    //finish aitc

        /**
         * Retrieve select object and join it to product entity table to get type ids
         *
         * @param  string $field
         * @param  mixed $value
         * @param  object $object
         * @return Zend_Db_Select
         */
        protected function _getLoadSelect($field, $value, $object)
        {
            return parent::_getLoadSelect($field, $value, $object)
                ->joinInner(array('p' => $this->getTable('catalog/product')), 'product_id=p.entity_id', 'type_id')
            ;
        }

        /**
         * Add join for catalog in stock field to product collection
         *
         * @param Mage_Catalog_Model_Entity_Product_Collection $productCollection
         * @return Mage_CatalogInventory_Model_Mysql4_Stock_Item
         */
        public function addCatalogInventoryToProductCollection($productCollection)
        {
    // start aitoc code
            $iWebsiteId = Mage::app()->getStore()->getWebsiteId();

            if (!$iWebsiteId)
            {
                $iWebsiteId = Mage::helper('aitquantitymanager')->getHiddenWebsiteId();
            }
    // finish aitoc code

            $isStockManagedInConfig = (int) Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
    #        $productCollection->joinTable('cataloginventory/stock_item',
            $productCollection->joinTable('aitquantitymanager/stock_item',
    #            'product_id=entity_id',
                'product_id=entity_id',
                array(
                    'is_saleable' => new Zend_Db_Expr('(IF(IF(use_config_manage_stock, ' . $isStockManagedInConfig . ', manage_stock), is_in_stock, 1))'),
                    'inventory_in_stock' => 'is_in_stock'
                ),
    #            null, 'left');
                '{{table}}.website_id = ' . $iWebsiteId, 'left'); // aitoc code

    #            d($productCollection->getSelect()->__toString(), 1);

            return $this;
        }
    }
}
elseif (version_compare(Mage::getVersion(), '1.4.1.0', 'ge'))
{
    class Aitoc_Aitquantitymanager_Model_Mysql4_Stock_Item extends Mage_CatalogInventory_Model_Mysql4_Stock_Item
    {
        protected function  _construct()
        {
    #        $this->_init('cataloginventory/stock_item', 'item_id');
            $this->_init('aitquantitymanager/stock_item', 'item_id');
        }

    // start aitoc    

        public function load(Mage_Core_Model_Abstract $object, $value, $field=null)
        {
            if (is_null($field)) {
                $field = $this->getIdFieldName();
            }

            $read = $this->_getReadAdapter();
            if ($read && !is_null($value)) {
                $select = $this->_getLoadSelect($field, $value, $object);
                $data = $read->fetchRow($select);

                if ($data) {
                    $object->setData($data);
                }
            }

            $this->_afterLoad($object);

            return $this;
        }


        public function getProductItemHash($iProductId, $stockId = 1)
        {
            $aItemHash = array();

            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable(), array('item_id', 'website_id', 'use_default_website_stock'))
                ->where('product_id =?', $iProductId)
                ->where('stock_id=?', $stockId)
                ->where('website_id <> 0');

            $aItemList = $this->_getReadAdapter()->fetchAll($select);

            if ($aItemList)
            {
                foreach ($aItemList as $aItem)
                {
                    $aItemHash[$aItem['website_id']] = $aItem;
                }
            }

            return $aItemHash;
        }

        public function getProductDefaultItem($iProductId, $stockId = 1)
        {

            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable(), '*')
                ->where('product_id =?', $iProductId)
                ->where('stock_id=?', $stockId)
                ->where('website_id=?', Mage::helper('aitquantitymanager')->getHiddenWebsiteId());
            return $this->_getReadAdapter()->fetchRow($select);
        }


    //fin aitoc


        /**
         * Loading stock item data by product
         *
         * @param   Mage_CatalogInventory_Model_Stock_Item $item
         * @param   int $productId
         * @return  Mage_Core_Model_Mysql4_Abstract
         */
        public function loadByProductId(Mage_CatalogInventory_Model_Stock_Item $item, $productId, $websiteId = null)
        {
            if (!$websiteId)
            {
                if ($iStoreId = Mage::registry('aitoc_order_refund_store_id')) // fix for refund
                {
                    $store = Mage::app()->getStore($iStoreId);
        #            d($store);
                    $websiteId = $store->getWebsiteId();
                }
                else 
                {
                    $websiteId = Mage::app()->getStore()->getWebsiteId();
                }

                if (!$websiteId)
                {
                    $websiteId = 1;
                }
    #            d($websiteId, 1);
            }

            $select = $this->_getLoadSelect('product_id', $productId, $item)
                ->where('stock_id=?', $item->getStockId())
                ->where('website_id=?', $websiteId);
    #$s = $select->__toString();            

            $item->setData($this->_getReadAdapter()->fetchRow($select));
            $this->_afterLoad($item);
    #        d($websiteId, 1);
            return $this;
        }

    // srart atioc
        public function getDataByProductId($iProductId, $iWebsiteId, $iStockId = 1)
        {
            $select = $this->_getLoadSelect('product_id', $iProductId, $this)
                ->where('stock_id=?', $iStockId)
                ->where('website_id=?', $iWebsiteId);

            return $this->_getReadAdapter()->fetchRow($select);
        }

    //finish aitc

        /**
         * Add join for catalog in stock field to product collection
         *
         * @param Mage_Catalog_Model_Entity_Product_Collection $productCollection
         * @return Mage_CatalogInventory_Model_Mysql4_Stock_Item
         */
        public function addCatalogInventoryToProductCollection($productCollection)
        {
    // start aitoc code  
            $iWebsiteId = Mage::app()->getStore()->getWebsiteId();

            if (!$iWebsiteId)
            {
                $iWebsiteId = Mage::helper('aitquantitymanager')->getHiddenWebsiteId();
            }
    // finish aitoc code  

            $isStockManagedInConfig = (int) Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
    #        $productCollection->joinTable('cataloginventory/stock_item',
            $productCollection->joinTable('aitquantitymanager/stock_item',
    #            'product_id=entity_id',
                'product_id=entity_id',
                array(
                    'is_saleable' => new Zend_Db_Expr('(IF(IF(use_config_manage_stock, ' . $isStockManagedInConfig . ', manage_stock), is_in_stock, 1))'),
                    'inventory_in_stock' => 'is_in_stock'
                ),
    #            null, 'left');
                '{{table}}.website_id = ' . $iWebsiteId, 'left'); // aitoc code

    #            d($productCollection->getSelect()->__toString(), 1);

            return $this;
        }
    }
}