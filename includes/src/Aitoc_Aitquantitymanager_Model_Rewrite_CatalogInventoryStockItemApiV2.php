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
class Aitoc_Aitquantitymanager_Model_Rewrite_CatalogInventoryStockItemApiV2 extends Aitoc_Aitquantitymanager_Model_Rewrite_CatalogInventoryStockItemApi
{
    /**
     * Update product stock data for website
     *
     * @param int $productId
     * @param object $data
     * @return bool
     */
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

        $defaultUpdate = true;

        $data = get_object_vars($data);

        if (isset($data['qty'][0])) {
            $data['qty'] = $data['qty'][0];
        }

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

    /**
     * Update stock inventory for multiple websites at once
     * @param int $productId
     * @param array of objects $data
     * @return bool
     */
    public function updateMultiple($productId, $data)
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

        if(is_array($data) && is_object($data[0])) {
            $dataNew = array();
            foreach($data as $dataSet) {
                $array = get_object_vars($dataSet);
                array_push($dataNew, $array);
            }
            $data = $dataNew;
        }

        foreach($data as $key => $dataSet) {
            if (isset($dataSet['qty'][0])) {
                $data[$key]['qty'] = $dataSet['qty'][0];
            }
        }

        if (is_array($data)) {
            $this->_stockUpdateWebsite($data, $product, $productId);
        }
        return true;
    }

    private function _stockUpdateWebsite($data, $product, $productId)
    {
        // updating stock data for websites
        foreach ($data as $stock)
        {
            if (isset($stock['website_id']))
            {
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

                foreach ($stock as $key => $value) {
                    $stockItem->setData($key, $value);
                }
                try {
                    $stockItem->save();
                } catch (Mage_Core_Exception $e) {
                    $this->_fault('not_updated', $e->getMessage());
                }
            }
            else {
                $this->_stockUpdateDefault($stock, $product);
            }
        }
    }
	
	protected function _getStockItem($productId, $stock)
    {
        return Mage::getModel('cataloginventory/stock_item')->loadByProductWebsite($productId, $stock['website_id']);
    }
}