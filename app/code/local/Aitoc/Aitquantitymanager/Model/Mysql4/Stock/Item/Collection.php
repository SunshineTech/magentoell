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
    class Aitoc_Aitquantitymanager_Model_Mysql4_Stock_Item_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
    {
        protected function _construct()
        {
    ///ait/        $this->_init('cataloginventory/stock_item');
            $this->_init('aitquantitymanager/stock_item');

        }

        /**
         * Add stock filter to collection
         *
         * @param   mixed $stock
         * @return  Mage_CatalogInventory_Model_Mysql4_Stock_Item_Collection
         */
        public function addStockFilter($stock)
        {
            if ($stock instanceof Mage_CatalogInventory_Model_Stock) {
                $this->addFieldToFilter('main_table.stock_id', $stock->getId());
            }
            else {
                $this->addFieldToFilter('main_table.stock_id', $stock);
            }
            return $this;
        }

        /**
         * Add product filter to collection
         *
         * @param   mixed $products
         * @return  Mage_CatalogInventory_Model_Mysql4_Stock_Item_Collection
         */
        public function addProductsFilter($products)
        {
            $productIds = array();
            foreach ($products as $product) {
                if ($product instanceof Mage_Catalog_Model_Product) {
                    $productIds[] = $product->getId();
                }
                else {
                    $productIds[] = $product;
                }
            }
            if (empty($productIds)) {
                $productIds[] = false;
                $this->_setIsLoaded(true);
            }
            $this->addFieldToFilter('main_table.product_id', array('in'=>$productIds));
            return $this;
        }

        /**
         * Join Stock Status to collection
         *
         * @param int $storeId
         * @return Mage_CatalogInventory_Model_Mysql4_Stock_Item_Collection
         */
        public function joinStockStatus($storeId = null)
        {
            $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
            $this->getSelect()->joinLeft(
                array('status_table' => $this->getTable('cataloginventory/stock_status')),
                '`main_table`.`product_id`=`status_table`.`product_id`'
                    . ' AND `main_table`.`stock_id`=`status_table`.`stock_id`'
                    . $this->getConnection()->quoteInto(' AND `status_table`.`website_id`=?', $websiteId),
                array('stock_status')
            );

            return $this;
        }

        public function addManagedFilter($isStockManagedInConfig)
        {
            if ($isStockManagedInConfig) {
                $this->getSelect()->where('(manage_stock = 1 OR use_config_manage_stock = 1)');
            } else {
                $this->addFieldToFilter('manage_stock', 1);
            }

            return $this;
        }

        public function addQtyFilter($comparsionMethod, $qty)
        {
            $allowedMethods = array('<', '>', '=', '<=', '>=', '<>');
            if (!in_array($comparsionMethod, $allowedMethods)) {
                Mage::throwException(Mage::helper('cataloginventory')->__('%s is not correct comparsion method.', $comparsionMethod));
            }
            $this->getSelect()->where("main_table.qty {$comparsionMethod} ?", $qty);
            return $this;
        }

        /**
         * Load data
         *
         * @return  Varien_Data_Collection_Db
         */
        public function load($printQuery = false, $logQuery = false)
        {
            if (!$this->isLoaded()) {
                $this->getSelect()->joinInner(array('_products_table' => $this->getTable('catalog/product')),
                    'main_table.product_id=_products_table.entity_id', 'type_id'
                );
            }
            return parent::load($printQuery, $logQuery);
        }
    }
}
elseif (version_compare(Mage::getVersion(), '1.4.1.0', 'ge'))
{
    class Aitoc_Aitquantitymanager_Model_Mysql4_Stock_Item_Collection extends Mage_CatalogInventory_Model_Mysql4_Stock_Item_Collection
    {
        protected function _construct()
        {
    ///ait/        $this->_init('cataloginventory/stock_item');
            $this->_init('aitquantitymanager/stock_item');
        }

    }
}