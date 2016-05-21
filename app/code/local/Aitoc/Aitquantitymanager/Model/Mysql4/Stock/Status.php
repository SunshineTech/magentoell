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
    class Aitoc_Aitquantitymanager_Model_Mysql4_Stock_Status extends Mage_Core_Model_Mysql4_Abstract
    {
        /**
         * Resource model initialization
         *
         */
        protected function _construct()
        {
    #        $this->_init('cataloginventory/stock_status', 'product_id');
            $this->_init('aitquantitymanager/stock_status', 'product_id');

    // tmp
    /*
            $oModel = Mage::getResourceModel('cataloginventory/stock_item');

            $sItemTable = $oModel->getTable('cataloginventory/stock_item');

            if (strpos($sItemTable, 'aitquantitymanager') !== false)
            {
                $sItemTable = str_replace('aitquantitymanager', 'cataloginventory', $sItemTable);
            }
            d(get_class_methods($oModel), 1);
            $select = $this->_getReadAdapter()->select()
                ->from(array('main_table' => $sItemTable) , '*')
                ->joinInner(array('p' => $this->getTable('catalog/product_website')), 'main_table.product_id=p.product_id', 'website_id')
                ->joinInner(array('ait_item' => $oModel->getTable('cataloginventory/stock_item')), 'ait_item.product_id=p.product_id AND ait_item.website_id = p.website_id', 'product_id')
    #            ->where('product_id =?', $iProductId)
    #            ->where('stock_id=?', $stockId)
                ->where('ait_item.website_id IS NOT NULL')
    ;
    #            ->where('website_id=?', Mage::helper('aitquantitymanager')->getHiddenWebsiteId());
    #        return $this->_getReadAdapter()->fetchRow($select);
            $aItemList = $this->_getReadAdapter()->fetchAll($select);



    d($aItemList);
    d($select->__toString());

            d($sItemTable);
    #
            #        d(get_class($oModel));

    #        d($oModel->getMainTable());
    #        d($oModel->getTable('cataloginventory/stock_item'));
    #d(get_class_methods($oModel));

    d(6,6);
            */
    // insert website
    /*

    INSERT into `catalog_product_website` (`product_id`, `website_id`)

    (SELECT

    `main_table`.`entity_id`,

    3 as 'website_id'


    FROM `catalog_product_entity` AS `main_table`)


    ///
    INSERT into `aitquantitymanager_stock_item`

    (SELECT

    null as 'item_id',

     `p`.`website_id`,
    `main_table`.`product_id`,
    `main_table`.`stock_id`,

    `main_table`.`qty`,
    `main_table`.`min_qty`,
    `main_table`.`use_config_min_qty`,
    `main_table`.`is_qty_decimal`,
    `main_table`.`backorders`,
    `main_table`.`use_config_backorders`,
    `main_table`.`min_sale_qty`,
    `main_table`.`use_config_min_sale_qty`,
    `main_table`.`max_sale_qty`,
    `main_table`.`use_config_max_sale_qty`,
    `main_table`.`is_in_stock`,
    `main_table`.`low_stock_date`,
    `main_table`.`notify_stock_qty`,
    `main_table`.`use_config_notify_stock_qty`,
    `main_table`.`manage_stock`,
    `main_table`.`use_config_manage_stock`,
    `main_table`.`stock_status_changed_automatically`,

    1 as 'use_default_website_stock'


    FROM `cataloginventory_stock_item` AS `main_table`
     INNER JOIN `catalog_product_website` AS `p` ON main_table.product_id=p.product_id
    LEFT JOIN `aitquantitymanager_stock_item` AS `ait_item` ON ait_item.product_id=p.product_id AND ait_item.website_id = p.website_id WHERE (ait_item.website_id IS NULL))
    */
    //////////////////////////////////////////////////////////////


    # `product_id`,`stock_id`,`website_id`
        }

    // start aitoc

        public function getProductStatusHash($productIds, $stockId = 1)
        {
            if (!is_array($productIds)) {
                $productIds = array($productIds);
            }

            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable(), array('website_id', 'stock_status'))
                ->where('product_id IN(?)', $productIds)
                ->where('stock_id=?', $stockId);
    //            ->where('website_id=?', $websiteId);
            return $this->_getReadAdapter()->fetchPairs($select);
        }

        public function getProductDefaultStatus($productId, $stockId = 1)
        {

            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable(), '*')
                ->where('product_id =?', $productId)
                ->where('stock_id=?', $stockId)
                ->where('website_id=?', Mage::helper('aitquantitymanager')->getHiddenWebsiteId());
            return $this->_getReadAdapter()->fetchRow($select);
        }


    //fin aitoc


        /**
         * Save Product Status per website
         *
         * @param Mage_CatalogInventory_Model_Stock_Status $object
         * @param int $productId
         * @param int $status
         * @param float $qty
         * @param int $stockId
         * @param int|null $websiteId
         * @return Mage_CatalogInventory_Model_Mysql4_Stock_Status
         */
        public function saveProductStatus(Mage_CatalogInventory_Model_Stock_Status $object, $productId, $status, $qty = 0, $stockId = 1, $websiteId = null)
        {
            $websites = array_keys($object->getWebsites($websiteId));

            foreach ($websites as $websiteId) {
                $select = $this->_getWriteAdapter()->select()
                    ->from($this->getMainTable())
                    ->where('product_id=?', $productId)
                    ->where('website_id=?', $websiteId)
                    ->where('stock_id=?', $stockId);
                if ($row = $this->_getWriteAdapter()->fetchRow($select)) {
                    $bind = array(
                        'qty'           => $qty,
                        'stock_status'  => $status
                    );
                    $where = array(
                        $this->_getWriteAdapter()->quoteInto('product_id=?', $row['product_id']),
                        $this->_getWriteAdapter()->quoteInto('website_id=?', $row['website_id']),
                        $this->_getWriteAdapter()->quoteInto('stock_id=?', $row['stock_id']),
                    );
                    $this->_getWriteAdapter()->update($this->getMainTable(), $bind, $where);
                }
                else {
                    $bind = array(
                        'product_id'    => $productId,
                        'website_id'    => $websiteId,
                        'stock_id'      => $stockId,
                        'qty'           => $qty,
                        'stock_status'  => $status
                    );
                    $this->_getWriteAdapter()->insert($this->getMainTable(), $bind);
                }
            }

            return $this;
        }

        /**
         * Retrieve product status
         * Return array as key product id, value - stock status
         *
         * @param int|array $productIds
         * @param int $websiteId
         * @param int $stockId
         *
         * @return array
         */
        public function getProductStatus($productIds, $websiteId, $stockId = 1)
        {
            if (!is_array($productIds)) {
                $productIds = array($productIds);
            }

            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable(), array('product_id', 'stock_status'))
                ->where('product_id IN(?)', $productIds)
                ->where('stock_id=?', $stockId)
                ->where('website_id=?', $websiteId);
    #d($select->__toString(), 1);
            return $this->_getReadAdapter()->fetchPairs($select);
        }

        /**
         * Retrieve product(s) data array
         *
         * @param int|array $productIds
         * @param int $websiteId
         * @param int $stockId
         *
         * @return array
         */
        public function getProductData($productIds, $websiteId, $stockId = 1)
        {
            if (!is_array($productIds)) {
                $productIds = array($productIds);
            }

            $data = array();

            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable())
                ->where('product_id IN(?)', $productIds)
                ->where('stock_id=?', $stockId)
                ->where('website_id=?', $websiteId);
            $query = $this->_getReadAdapter()->query($select);
            while ($row = $query->fetch()) {
                $data[$row['product_id']] = $row;
            }
            return $data;
        }

        /**
         * Retrieve websites and default stores
         * Return array as key website_id, value store_id
         *
         * @return array
         */
        public function getWebsiteStores() {
            $select = Mage::getModel('core/website')->getDefaultStoresSelect(false);
            return $this->_getReadAdapter()->fetchPairs($select);
        }

        /**
         * Retrieve Product Type
         *
         * @param array|int $productIds
         * @return array
         */
        public function getProductsType($productIds)
        {
            if (!is_array($productIds)) {
                $productIds = array($productIds);
            }

            $select = $this->_getReadAdapter()->select()
                ->from(
                    array('e' => $this->getTable('catalog/product')),
                    array('entity_id', 'type_id'))
                ->where('entity_id IN(?)', $productIds);
            return $this->_getReadAdapter()->fetchPairs($select);
        }

        /**
         * Retrieve Product part Collection array
         * Return array as key product id, value product type
         *
         * @param int $lastEntityId
         * @param int $limit
         * @return array
         */
        public function getProductCollection($lastEntityId = 0, $limit = 1000) {
            $select = $this->_getReadAdapter()->select()
                ->from(
                    array('e' => $this->getTable('catalog/product')),
                    array('entity_id', 'type_id'))
                ->order('entity_id ASC')
                ->where('entity_id>?', $lastEntityId)
                ->limit($limit);
            return $this->_getReadAdapter()->fetchPairs($select);
        }

        /**
         * Add stock status to prepare index select
         *
         * @param Varien_Db_Select $select
         * @param Mage_Core_Model_Website $website
         * @return Mage_CatalogInventory_Model_Mysql4_Stock_Status
         */
        public function addStockStatusToSelect(Varien_Db_Select $select, Mage_Core_Model_Website $website)
        {
            $websiteId = $website->getId();
            $select->joinLeft(
                array('stock_status' => $this->getMainTable()),
                'e.entity_id=stock_status.product_id AND stock_status.website_id='.$websiteId,
                array('salable' => 'stock_status.stock_status')
            );

            return $this;
        }

        public function prepareCatalogProductIndexSelect(Varien_Db_Select $select, $entityField, $websiteField)
        {
            $select->join(
                array('ciss' => $this->getMainTable()),
                "ciss.product_id = {$entityField} AND ciss.website_id = {$websiteField}",
                array()
            );
            $select->where('ciss.stock_status=?', Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK);

            return $this;
        }
    }
}
elseif (version_compare(Mage::getVersion(), '1.4.1.0', 'ge'))
{
    class Aitoc_Aitquantitymanager_Model_Mysql4_Stock_Status extends Mage_CatalogInventory_Model_Mysql4_Stock_Status
    {
        /**
         * Resource model initialization
         *
         */
        protected function _construct()
        {
    #        $this->_init('cataloginventory/stock_status', 'product_id');
            $this->_init('aitquantitymanager/stock_status', 'product_id');

    // tmp
    /*
            $oModel = Mage::getResourceModel('cataloginventory/stock_item');

            $sItemTable = $oModel->getTable('cataloginventory/stock_item');

            if (strpos($sItemTable, 'aitquantitymanager') !== false)
            {
                $sItemTable = str_replace('aitquantitymanager', 'cataloginventory', $sItemTable);
            }
            d(get_class_methods($oModel), 1);
            $select = $this->_getReadAdapter()->select()
                ->from(array('main_table' => $sItemTable) , '*')
                ->joinInner(array('p' => $this->getTable('catalog/product_website')), 'main_table.product_id=p.product_id', 'website_id')
                ->joinInner(array('ait_item' => $oModel->getTable('cataloginventory/stock_item')), 'ait_item.product_id=p.product_id AND ait_item.website_id = p.website_id', 'product_id')
    #            ->where('product_id =?', $iProductId)
    #            ->where('stock_id=?', $stockId)
                ->where('ait_item.website_id IS NOT NULL')
    ;
    #            ->where('website_id=?', Mage::helper('aitquantitymanager')->getHiddenWebsiteId());
    #        return $this->_getReadAdapter()->fetchRow($select);
            $aItemList = $this->_getReadAdapter()->fetchAll($select);



    d($aItemList);
    d($select->__toString());

            d($sItemTable);
    #
            #        d(get_class($oModel));

    #        d($oModel->getMainTable());
    #        d($oModel->getTable('cataloginventory/stock_item'));
    #d(get_class_methods($oModel));

    d(6,6);
            */
    // insert website
    /*

    INSERT into `catalog_product_website` (`product_id`, `website_id`)

    (SELECT

    `main_table`.`entity_id`,

    3 as 'website_id'


    FROM `catalog_product_entity` AS `main_table`)


    ///
    INSERT into `aitquantitymanager_stock_item`

    (SELECT

    null as 'item_id',

     `p`.`website_id`,
    `main_table`.`product_id`,
    `main_table`.`stock_id`,

    `main_table`.`qty`,
    `main_table`.`min_qty`,
    `main_table`.`use_config_min_qty`,
    `main_table`.`is_qty_decimal`,
    `main_table`.`backorders`,
    `main_table`.`use_config_backorders`,
    `main_table`.`min_sale_qty`,
    `main_table`.`use_config_min_sale_qty`,
    `main_table`.`max_sale_qty`,
    `main_table`.`use_config_max_sale_qty`,
    `main_table`.`is_in_stock`,
    `main_table`.`low_stock_date`,
    `main_table`.`notify_stock_qty`,
    `main_table`.`use_config_notify_stock_qty`,
    `main_table`.`manage_stock`,
    `main_table`.`use_config_manage_stock`,
    `main_table`.`stock_status_changed_automatically`,

    1 as 'use_default_website_stock'


    FROM `cataloginventory_stock_item` AS `main_table`
     INNER JOIN `catalog_product_website` AS `p` ON main_table.product_id=p.product_id
    LEFT JOIN `aitquantitymanager_stock_item` AS `ait_item` ON ait_item.product_id=p.product_id AND ait_item.website_id = p.website_id WHERE (ait_item.website_id IS NULL))
    */
    //////////////////////////////////////////////////////////////


    # `product_id`,`stock_id`,`website_id`
        }

    // start aitoc

        public function getProductStatusHash($productIds, $stockId = 1)
        {
            if (!is_array($productIds)) {
                $productIds = array($productIds);
            }

            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable(), array('website_id', 'stock_status'))
                ->where('product_id IN(?)', $productIds)
                ->where('stock_id=?', $stockId);
    //            ->where('website_id=?', $websiteId);
            return $this->_getReadAdapter()->fetchPairs($select);
        }

        public function getProductDefaultStatus($productId, $stockId = 1)
        {

            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable(), '*')
                ->where('product_id =?', $productId)
                ->where('stock_id=?', $stockId)
                ->where('website_id=?', Mage::helper('aitquantitymanager')->getHiddenWebsiteId());
            return $this->_getReadAdapter()->fetchRow($select);
        }


    //fin aitoc

    }
}