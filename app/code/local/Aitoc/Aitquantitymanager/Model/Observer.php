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
 * @copyright  Copyright (c) 2011 AITOC, Inc.
 */
    class Aitoc_Aitquantitymanager_Model_Observer
    {
        public function onAdminhtmlSalesOrderCreditmemoRegisterBefore($observer)
        {
            if (version_compare( Mage::getVersion(), '1.4.0.0', 'ge') && version_compare( Mage::getVersion(), '1.4.1.0', 'lt'))
                return;

            $creditmemo = $observer->getCreditmemo();
            foreach ($creditmemo->getAllItems() as $creditmemoItem)
            {
                if ($creditmemoItem->getStoreId() && Mage::registry('aitoc_order_refund_store_id') === NULL)
                {
                    Mage::register('aitoc_order_refund_store_id', $creditmemoItem->getStoreId());
                    return true;
                }
            }
        }

        /*
        * used to prevent database locks conflicts during repeated saving stock items from some of the modules classes
        * due to this second saving process takes place only when first saving was committed
        */
        public function onCataloginventoryStockItemSaveCommitAfter($observer)
        {
            $stockItem = $observer->getItem();
            $callingClass = $stockItem->getCallingClass();
            
            if($callingClass == 'Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogInventoryStock')
                $this->_onCataloginventoryStockItemSaveCommitAfter1($stockItem);
            elseif($callingClass == 'Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogInventoryObserver' || 
                   $callingClass == 'Aitoc_Aitquantitymanager_AttributeController')
                $this->_onCataloginventoryStockItemSaveCommitAfter2($stockItem);
            
            $stockItem->setCallingClass('');            
            return;
        }

        /*
        * @param Mage_Cataloginventory_Model_Stock_Item $oSavedItem
        */
        protected function _onCataloginventoryStockItemSaveCommitAfter1($oSavedItem)
        {
            if ($oSavedItem->getUseDefaultWebsiteStock())
            {
                if (!$oSavedItem) return false;

                $productId = $oSavedItem->getProductId();
                if (!$productId) return false;
        
                $aNewData = $oSavedItem->getData();
                $aExistRecords = $oSavedItem->getProductItemHash($productId);
     
                foreach ($aExistRecords as $iWebsiteId => $aItemData)
                {
                    $sSaveMode = '';
            
                    if (
                        ($iWebsiteId == Mage::helper('aitquantitymanager')->getHiddenWebsiteId()) 
                        OR 
                        ($aItemData['item_id'] != $oSavedItem->getId() AND $aItemData['use_default_website_stock'])
                       )
                    {
                        $sSaveMode = 'edit';
                    }
                
                    if ($sSaveMode)
                    {
                        $oNewItem = Mage::getModel('cataloginventory/stock_item');
                        $oNewItem->addData($aNewData);
                        $oNewItem->setSaveWebsiteId($iWebsiteId);
                
                        if ($sSaveMode == 'edit')
                        {
                            $oNewItem->setId($aExistRecords[$iWebsiteId]['item_id']);
                        }
                        else 
                        {
                            $oNewItem->setId(null);
                        }
                 
                        $oNewItem->setCallingClass('');
                        $oNewItem->save();
                    }
                }
            
            return true;
            }
        }

        /*
        * @param Mage_Cataloginventory_Model_Stock_Item $item
        */
        protected function _onCataloginventoryStockItemSaveCommitAfter2($item)
        {
            $aWebsiteSaveIds[] = $item->getWebsiteId();
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $aDefaultData = $item->getProductDefaultItem($product->getId());
            $aProductWebsiteHash = $product->getWebsiteIds();
        
            if ($aProductWebsiteHash)
            {
                $aExistRecords = $item->getProductItemHash($product->getId());
            
                foreach ($aProductWebsiteHash as $iWebsiteId)
                {
                    $sSaveMode = '';
                
                    if (isset($aExistRecords[$iWebsiteId]))
                    {
                        if ($aExistRecords[$iWebsiteId]['use_default_website_stock'])
                        {
                            $sSaveMode = 'edit';
                        }
                    }
                    else 
                    {
                        if ($iWebsiteId != Mage::helper('aitquantitymanager')->getHiddenWebsiteId())
                        {    
                            $sSaveMode = 'add';
                        }
                    }
                                
                    if ($sSaveMode)
                    {
                        $oNewItem = Mage::getModel('cataloginventory/stock_item');
                        $oNewItem->addData($aDefaultData);
                        $oNewItem->setSaveWebsiteId($iWebsiteId);
                        if ($sSaveMode == 'edit')
                        {
                            $oNewItem->setId($aExistRecords[$iWebsiteId]['item_id']);
                        }
                        else 
                        {
                            $oNewItem->setId(null);
                        }
                    
                        $oNewItem->setUseDefaultWebsiteStock(1);
                        $oNewItem->setTypeId($item->getTypeId());
                        $oNewItem->setProductName($item->getProductName());
                        $oNewItem->setStoreId($item->getStoreId());
                        $oNewItem->setProductTypeId($item->getProductTypeId());
                        $oNewItem->setProductStatusChanged($item->getProductStatusChanged());

                        $oNewItem->setCallingClass('');
                        $oNewItem->save();
                   
                        $aWebsiteSaveIds[] = $iWebsiteId;
                    }
                }
            }
        
            if ($aWebsiteSaveIds AND !Mage::registry('aitquantitymanager_website_save_ids'))
            {
                Mage::register('aitquantitymanager_website_save_ids', $aWebsiteSaveIds);
            }

            return true;
        }

        public function onRssCatalogNotifyStockCollectionSelect($observer)
        {
            $collection = $observer->getCollection();
            //$stockItemTable = $collection->getTable('cataloginventory/stock_item');
            $collection->getSelect()->columns(array('website_id' => 'invtr.website_id'));
        }
    }