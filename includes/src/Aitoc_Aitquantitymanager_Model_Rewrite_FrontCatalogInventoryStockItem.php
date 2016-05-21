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

/* AITOC static rewrite inserts start */
/* $meta=%default,Aitoc_Aitpreorder% */
if(Mage::helper('core')->isModuleEnabled('Aitoc_Aitpreorder')){
    class Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogInventoryStockItem_Aittmp extends Aitoc_Aitpreorder_Model_Rewrite_StockItem {} 
 }else{
    /* default extends start */
    class Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogInventoryStockItem_Aittmp extends Mage_CatalogInventory_Model_Stock_Item {}
    /* default extends end */
}

/* AITOC static rewrite inserts end */
class Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogInventoryStockItem extends Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogInventoryStockItem_Aittmp
{
    // overide parent
    protected function _construct()
    {
        Mage::getModel('aitquantitymanager/moduleObserver')->onAitocModuleLoad();
        
///ait/        $this->_init('cataloginventory/stock_item');
        $this->_init('aitquantitymanager/stock_item');
    }

    // overide parent
    public function loadByProduct($product, $iWebsiteId = 0)
    {
        if ($product instanceof Mage_Catalog_Model_Product) {
            $product = $product->getId();
        }
        
// start aitoc code  

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
            } else if ($oRequest->getParam('group')) {
                $storeIds = Mage::app()->getGroup($oRequest->getParam('group'))->getStoreIds();
                $iStoreId = array_pop($storeIds);
            } else if ($oRequest->getParam('store')) {
                $iStoreId = (int)$oRequest->getParam('store');
            } elseif ($oRequest->getParam('store_id')) {
                $iStoreId = (int)$oRequest->getParam('store_id');
            } elseif (Mage::getSingleton('adminhtml/session_quote')->getStoreId()) {
                $iStoreId = Mage::getSingleton('adminhtml/session_quote')->getStoreId();
            } else {
                $iStoreId = '';
            }
        }  
        
        if (!$iStoreId) 
        {
            $iStoreId = $this->getStoreId(); 
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
// finish aitoc code        
        
#        $this->_getResource()->loadByProductId($this, $product);
        $this->_getResource()->loadByProductId($this, $product, $iWebsiteId); // aitoc code        

        $this->setOrigData();
        return $this;
    }

// start aitoc code    
    public function loadByProductWebsite($product, $iWebsiteId)
    {
        if (!$iWebsiteId) return false;
        
        if ($product instanceof Mage_Catalog_Model_Product) {
            $product = $product->getId();
        }
        
        $this->_getResource()->loadByProductId($this, $product, $iWebsiteId); // aitoc code        

        $this->setOrigData();
        return $this;
    }

    public function getProductItemHash($iProductId)
    {
        return $this->_getResource()->getProductItemHash($iProductId);
    }
    
    public function getProductDefaultItem($iProductId)
    {
        return $this->_getResource()->getProductDefaultItem($iProductId);
    }
// finish aitoc code    

    // overide parent
    public function assignProduct(Mage_Catalog_Model_Product $product)
    {
        if (!$this->getId() || !$this->getProductId()) 
        {
// start aitoc code            
            $iWebsiteId = $product->getStore()->getWebsiteId();
                
            $iHiddenWebsiteId = Mage::helper('aitquantitymanager')->getHiddenWebsiteId();

            if (!$iWebsiteId)
            {
                $iWebsiteId = 1; // default
                
                if (!$this->getId())
                {
                    $iWebsiteId = $iHiddenWebsiteId;
                }
            }
            
            $oAitocItem = Mage::getResourceModel('aitquantitymanager/stock_item');
            $oAitocItem->loadByProductId($this, $product->getId(), $iWebsiteId);
            if (!$this->getId() AND $iWebsiteId != $iHiddenWebsiteId)
            {
                $iWebsiteId = $iHiddenWebsiteId; // default
                $oAitocItem->loadByProductId($this, $product->getId(), $iWebsiteId);
                $this->setId(null); 
            }
        }
// finish aitoc code

        $this->setProduct($product);
        $product->setStockItem($this);
        $product->setIsInStock($this->getIsInStock());
        Mage::getSingleton('cataloginventory/stock_status')
            ->assignProduct($product, $this->getStockId(), $this->getStockStatus());
        return $this;
    }


// start aitoc code
    public function getStoreById($id)
    {
        $this->_initStores();
        /**
         * In single store mode all data should be saved as default
         */
        if (Mage::app()->isSingleStoreMode()) {
            return Mage::app()->getStore(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
        }

        if (isset($this->_storesIdCode[$id])) {
            return $this->getStoreByCode($this->_storesIdCode[$id]);
        }
        return false;
    }

// finish aitoc code
    
    
    /**
     * Before save prepare process
     *
     * @return Mage_CatalogInventory_Model_Stock_Item
     */
    protected function _beforeSave()
    {
        
// start aitoc  code      

        if ($iWebsiteId = $this->getSaveWebsiteId())
        {
            // get from observer
        }
        else 
        {
            if ($iStoreId = Mage::registry('aitoc_order_refund_store_id')) // fix for refund
            {
                $store = Mage::app()->getStore($iStoreId);
                $iWebsiteId = $store->getWebsiteId();
                
                Mage::unregister('aitoc_order_refund_store_id');
            }
            elseif ($iStoreId = Mage::registry('aitoc_order_create_store_id')) // fix for refund
            {
                $store = Mage::app()->getStore($iStoreId);
                $iWebsiteId = $store->getWebsiteId();
            }
            elseif (Mage::registry('aitoc_api_update_website_id'))
            {
                $iWebsiteId = Mage::registry('aitoc_api_update_website_id');}
            else 
            {
                $iWebsiteId = 0;
            }
        }
        
        if ($controller = Mage::app()->getFrontController()) 
        {
            $oRequest = $controller->getRequest();
            $iStoreId = (int)$oRequest->getParam('store');
        }
        else 
        {
            $iStoreId = 0;
        }
        
        if (!$iStoreId) 
        {
            $iStoreId = $this->getStoreId();
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

        if (!Mage::registry('reindex_in_progress') && !Mage::registry('aitoc_api_update'))
        {
            // do not replace website_id if update from API
            $this->setWebsiteId($iWebsiteId);
        }
// finish aitoc code
        
        // see if quantity is defined for this item type
        $typeId = $this->getTypeId();
        if ($productTypeId = $this->getProductTypeId()) {
            $typeId = $productTypeId;
        }
        $isQty = Mage::helper('catalogInventory')->isQty($typeId);

        if ($isQty) {
            if ($this->getBackorders() == Mage_CatalogInventory_Model_Stock::BACKORDERS_NO
                && $this->getQty() <= $this->getMinQty()) {
                $this->setIsInStock(false)
                    ->setStockStatusChangedAutomaticallyFlag(true);
            }

            // if qty is below notify qty, update the low stock date to today date otherwise set null
            $this->setLowStockDate(null);
            if ((float)$this->getQty() < $this->getNotifyStockQty()) {
                $this->setLowStockDate(Mage::app()->getLocale()->date(null, null, null, false)
                    ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)
                );
            }

            $this->setStockStatusChangedAutomatically(0);
            if ($this->hasStockStatusChangedAutomaticallyFlag()) {
                $this->setStockStatusChangedAutomatically((int)$this->getStockStatusChangedAutomaticallyFlag());
            }
        }
        else {
            $this->setQty(0);
        }

        Mage::dispatchEvent('cataloginventory_stock_item_save_before', array('item' => $this));
        
        return $this;
    }
    
    // override parent
    public function afterCommitCallback()
    {
        parent::afterCommitCallback();
        
        Mage::getSingleton('index/indexer')->processEntityAction(
            $this, self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
        );#
        return $this;
    }    
    
}