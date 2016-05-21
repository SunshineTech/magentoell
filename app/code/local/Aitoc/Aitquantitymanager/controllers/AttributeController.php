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
require_once 'Mage/Adminhtml/controllers/Catalog/Product/Action/AttributeController.php';

class Aitoc_Aitquantitymanager_AttributeController extends Mage_Adminhtml_Catalog_Product_Action_AttributeController
#class Mage_Adminhtml_Catalog_Product_Action_AttributeController extends Mage_Adminhtml_Controller_Action
{

    public function saveAction()
    {
        if (!$this->_validateProducts()) {
            return;
        }

        /* Collect Data */
        $inventoryData      = $this->getRequest()->getParam('inventory', array());
        $attributesData     = $this->getRequest()->getParam('attributes', array());
        $websiteRemoveData  = $this->getRequest()->getParam('remove_website_ids', array());
        $websiteAddData     = $this->getRequest()->getParam('add_website_ids', array());

        /* Prepare inventory data item options (use config settings) */
        foreach (Mage::helper('cataloginventory')->getConfigItemOptions() as $option) {
            if (isset($inventoryData[$option]) && !isset($inventoryData['use_config_' . $option])) {
                $inventoryData['use_config_' . $option] = 0;
            }
        }

        try {
            if ($attributesData) {
                $dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                $storeId    = $this->_getHelper()->getSelectedStoreId();

                foreach ($attributesData as $attributeCode => $value) {
                    $attribute = Mage::getSingleton('eav/config')
                        ->getAttribute('catalog_product', $attributeCode);
                    if (!$attribute->getAttributeId()) {
                        unset($attributesData[$attributeCode]);
                        continue;
                    }
                    if ($attribute->getBackendType() == 'datetime') {
                        if (!empty($value)) {
                            $filterInput    = new Zend_Filter_LocalizedToNormalized(array(
                                'date_format' => $dateFormat
                            ));
                            $filterInternal = new Zend_Filter_NormalizedToLocalized(array(
                                'date_format' => Varien_Date::DATE_INTERNAL_FORMAT
                            ));
                            $value = $filterInternal->filter($filterInput->filter($value));
                        } else {
                            $value = null;
                        }
                        $attributesData[$attributeCode] = $value;
                    }
                    elseif (version_compare(Mage::getVersion(), '1.5.0.0', '>=') && $attribute->getFrontendInput() == 'multiselect') {
                        // Check if 'Change' checkbox has been checked by admin for this attribute
                        $isChanged = (bool)$this->getRequest()->getPost($attributeCode . '_checkbox');
                        if (!$isChanged && version_compare(Mage::getVersion(), '1.7.0.0', '>=')) {
                            unset($attributesData[$attributeCode]);
                            continue;
                        }
                        if (is_array($value)) {
                            $value = implode(',', $value);
                        }
                        $attributesData[$attributeCode] = $value;
                    }
                }

                Mage::getSingleton('catalog/product_action')
                    ->updateAttributes($this->_getHelper()->getProductIds(), $attributesData, $storeId);
            }
            
            if ($inventoryData) {
                $stockItem = Mage::getModel('cataloginventory/stock_item');
                $stockItemSaved = false;
                if(version_compare(Mage::getVersion(), '1.6.2.0', '>='))
                {
                    $stockItem->setProcessIndexEvents(false);                    
                }

                foreach ($this->_getHelper()->getProductIds() as $productId) {
                    $stockItem->setData(array());
                    $stockItem->loadByProduct($productId)
                        ->setProductId($productId);

                    $stockDataChanged = false;
                    foreach ($inventoryData as $k => $v) {
                        $stockItem->setDataUsingMethod($k, $v);
                        if ($stockItem->dataHasChangedFor($k)) {
                            $stockDataChanged = true;
                        }
                    }
                    
                    
// start aitoc code
                    $iStoreId = 0;
                    $iWebsiteId = 0;
                                
                    if ($controller = Mage::app()->getFrontController()) 
                    {
                        $oRequest = $controller->getRequest();
                        if ($oRequest->getParam('store')) {
                            $iStoreId = (int)$oRequest->getParam('store');
                        }
                    }
                            
                    if ($iStoreId) 
                    {
                        $store = Mage::app()->getStore($iStoreId);
                        $iWebsiteId = $store->getWebsiteId();
                    }
                    
                    $oItem = Mage::getResourceModel('cataloginventory/stock_item');
                    
                    if ($iWebsiteId)
                    {
                        $aItemData = $oItem->getDataByProductId($productId, $iWebsiteId);
                    }
                    else 
                    {
                        $aItemData = 1;
                    }
                    
// finish aitoc code                       
#                    d($stockItem->getdata());
                    
//                    if ($stockDataChanged) {
                    if ($stockDataChanged AND $aItemData) { // aitoc code
                        $stockItem->setCallingClass('Aitoc_Aitquantitymanager_AttributeController');//Aitoc customization - used in Aitoc_Aitquantitymanager_Model_Observer observer. In the cataloginventory_stock_item_save_commit_after event.
                        $stockItem->save();
                        if(version_compare(Mage::getVersion(), '1.6.2.0', '>='))
                        {
                            $stockItemSaved = true;
                        }                                       
                    }
                }
                if (version_compare(Mage::getVersion(), '1.6.2.0', '>=') && $stockItemSaved) {
                    Mage::getSingleton('index/indexer')->indexEvents(
                        Mage_CatalogInventory_Model_Stock_Item::ENTITY,
                        Mage_Index_Model_Event::TYPE_SAVE
                    );
                }
            }
            if ($websiteAddData || $websiteRemoveData) {
                /* @var $actionModel Mage_Catalog_Model_Product_Action */
                $actionModel = Mage::getSingleton('catalog/product_action');
                $productIds  = $this->_getHelper()->getProductIds();

                if ($websiteRemoveData) {
                    $actionModel->updateWebsites($productIds, $websiteRemoveData, 'remove');
                }
                if ($websiteAddData) {
                    $actionModel->updateWebsites($productIds, $websiteAddData, 'add');
                }

                /**
                 * @deprecated since 1.3.2.2
                 */
                Mage::dispatchEvent('catalog_product_to_website_change', array(
                    'products' => $productIds
                ));

                $this->_getSession()->addNotice(
                    $this->__('Please refresh "Catalog Url Rewrites" and "Product Attributes" in System -> <a href="%s">Index Management</a>', $this->getUrl('adminhtml/process/list'))
                );
            }

            // fix for empty items for new websites
            
            $sItemTable = Mage::getSingleton('core/resource')->getTableName('aitquantitymanager/stock_item');
            $sProductTable = Mage::getSingleton('core/resource')->getTableName('catalog/product');
            $sWebsiteTable = Mage::getSingleton('core/resource')->getTableName('catalog/product_website');
#d($this->_getHelper()->getProductIds());
            
            $oDb = Mage::getSingleton('core/resource')->getConnection('core_write');            

            $select  = $oDb->select()
                ->from(array('e' => $sProductTable), array('entity_id'));
            $select->joinLeft(
                array('pw' => $sWebsiteTable),
                "pw.product_id = e.entity_id",
                array('pw.website_id')
            )
            ->joinLeft(
                array('item' => $sItemTable),
                'item.product_id = e.entity_id AND pw.website_id = item.website_id', // aitoc code
                array())
            ->where('item.item_id IS null')
            ->where('e.entity_id IN (' . implode(',', $this->_getHelper()->getProductIds()) . ')');

            $aMissingItems = $oDb->fetchAll($select->__toString());

            if ($aMissingItems)
            {
                $stockItem = Mage::getModel('cataloginventory/stock_item');  
                
                foreach ($aMissingItems as $aItem)
                {
                    $product = Mage::getModel('catalog/product')->load($aItem['entity_id']);
                    
                    $aDefaultData = $stockItem->getProductDefaultItem($aItem['entity_id']);

                    $oNewItem = Mage::getModel('cataloginventory/stock_item');
                    $oNewItem->addData($aDefaultData);
                
                    $oNewItem->setSaveWebsiteId($aItem['website_id']);
                    
                    $oNewItem->setId(null);
                    
                    $oNewItem->setUseDefaultWebsiteStock(1);
                    $oNewItem->setTypeId($product->getTypeId());
                    
#                    $oNewItem->setProductName($stockItem->getProductName());
#                    $oNewItem->setStoreId($stockItem->getStoreId());
#                    $oNewItem->setProductTypeId($stockItem->getProductTypeId());
#                    $oNewItem->setProductStatusChanged($stockItem->getProductStatusChanged());

#                    $aWebsiteSaveIds = array($iWebsiteId);
/*
                    if ($aWebsiteSaveIds AND !Mage::registry('aitquantitymanager_website_save_ids'))
                    {
                        Mage::register('aitquantitymanager_website_save_ids', $aWebsiteSaveIds);
                    }
*/
                    $oNewItem->save();
                }
            }

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) were successfully updated',
                count($this->_getHelper()->getProductIds()))
            );
        
        }
        
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('There was an error while updating product(s) attributes'));
        }

        $this->_redirect('*/catalog_product/', array('store'=>$this->_getHelper()->getSelectedStoreId()));
    }
}