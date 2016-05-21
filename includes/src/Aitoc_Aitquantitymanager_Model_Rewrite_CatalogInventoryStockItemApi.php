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
class Aitoc_Aitquantitymanager_Model_Rewrite_CatalogInventoryStockItemApi extends Mage_CatalogInventory_Model_Stock_Item_Api
{

    var $_complexProductTypes = array('configurable', 'bundle', 'grouped');

    public function items($productIds)
    {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }

        $product = Mage::getModel('catalog/product');

    
        
        foreach ($productIds as &$productId) {
            if ($newId = $product->getIdBySku($productId)) {
                $productId = $newId;
            }
        }

        $collection = Mage::getModel('catalog/product')
            ->getCollection()
            ->setFlag('require_stock_items', true)
            ->addFieldToFilter('entity_id', array('in'=>$productIds));

        $result = array();

        foreach ($collection as $product) {

  
            if ($product->getStockItem()) {
                
                $stock = array(); // stock data for all websites
                $stockCollection = Mage::getResourceModel('cataloginventory/stock_item_collection')->addProductsFilter(array($product))->load();
                $maxWebsite_id = 0;
                foreach ($stockCollection as $stockItem)
                {
                    $tmpStock = $stockItem->getData();
                    if($maxWebsite_id < $tmpStock['website_id'])
                    {
                        $maxWebsite_id = $tmpStock['website_id'];
                    }
                    if(in_array($product->getTypeId(),$this->_complexProductTypes))
                    {
                        $this->_filterComplexProductValues($tmpStock);
                    }
                    $stock[] = $tmpStock;
                }
                
                foreach($stock as $key => $value)
                {
                    if($value['website_id'] == $maxWebsite_id || empty($value['website_id']))
                    {
                        unset($stock[$key]);
                    }
                }
                $stock = array_values($stock);                    
                //$result = $product->getStockItem()->getData();
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId(), 0)->getData();
                if(in_array($product->getTypeId(),$this->_complexProductTypes))
                {
                    $this->_filterComplexProductValues($stockItem);
                }
                $stockItem['sku'] = $product->getSku();
                $stockItem['stock'] = $stock;

                if(isset($stockItem['website_id']))
                {
                    unset($stockItem['website_id']);
                }

                $result[] = $stockItem;
            }
        }
        return $result;
    }
    
    public function update($productId, $data)
    {
        $product = Mage::getModel('catalog/product');

        if ($newId = $product->getIdBySku($productId)) {
            $productId = $newId;
        }

        $product->setStoreId($this->_getStoreId())
            ->load($productId);

        if (!$product->getId()) {
            $this->_fault('not_exists');
        }

        /*if (!$stockData = $product->getStockItem()->getData()) {
            $stockData = array();
        }*/

        $defaultUpdate = true;
        
        if (isset($data[0]) && is_array($data[0])) {
            $defaultUpdate = false;
        }

        if (isset($data['website_id'])) {
            $defaultUpdate = false;
            $data = array($data);
        }               
        
        if (is_array($data) && !$defaultUpdate) {
            $this->_updateWebsite($data, $productId, $product);
        } else {
            $this->_stockUpdateDefault($data, $product);
        }

        return true;
    }
    
    private function _filterComplexProductValues(&$productData)
    {
        $validKeys = array(
            'item_id',
            'website_id',
            'product_id',
            'stock_id',
            'manage_stock', 
            'use_config_manage_stock',       
            'enable_qty_increments',
            'use_config_enable_qty_increments',
            'qty_increments',
            'use_config_qty_increments',
            'stock_availability',
            'is_in_stock',
        );
        foreach($productData as $key => $value) {
            if(!in_array($key, $validKeys)) {
                unset($productData[$key]);
            }
        }
    }

    protected function _stockUpdateDefault($data, $product)
    {
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId(), 0);

        foreach ($data as $key => $value) {
            $stockItem->setData($key, $value);
        }

        try {
            $stockItem->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('not_updated', $e->getMessage());
        }
    }

	protected function _updateWebsite($data, $productId, $product)
    {
        // updating stock data for websites
        foreach ($data as $stock)
        {
            if (!isset($stock['website_id']))
            {
                continue;
            }
            if(!Mage::registry('aitoc_api_update')) {
                Mage::register('aitoc_api_update', true);
            }

            $stockItem = $this->_getStockItem($productId, $stock);
            if(!$stockItem->getId())
            {
                //product data for this website is not available
                $this->_fault('not_updated', Mage::helper('aitquantitymanager')->__('Inventory for product id %s on website id %s is not available and can\'t be updated', $productId, $stock['website_id']));
                break;
            }

            if(isset($stock['use_default_website_stock']) && $stock['use_default_website_stock'])
            {
                $defaultData = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId(), 0)->getData();
                unset($defaultData['website_id']);
                unset($defaultData['item_id']);
                $stock = $defaultData;
            }

            foreach ($stock as $key => $value)
            {
                $stockItem->setData($key, $value);
            }

            try {
                $stockItem->save();
            } catch (Mage_Core_Exception $e) {
                $this->_fault('not_updated', $e->getMessage());
            }
        }
    }

    protected function _getStockItem($productId, $stock)
    {
        return Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId, $stock['website_id']);
    }
}