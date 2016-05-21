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
/* $meta=%default,Aitoc_Aitpermissions,Aitoc_Aitpreorder% */
if(Mage::helper('core')->isModuleEnabled('Aitoc_Aitpreorder')){
    class Aitoc_Aitquantitymanager_Block_Rewrite_AdminCatalogProductEditTabInventory_Aittmp extends Aitoc_Aitpreorder_Block_Rewrite_AdminhtmlCatalogProductEditTabInventory {} 
 }elseif(Mage::helper('core')->isModuleEnabled('Aitoc_Aitpermissions')){
    class Aitoc_Aitquantitymanager_Block_Rewrite_AdminCatalogProductEditTabInventory_Aittmp extends Aitoc_Aitpermissions_Block_Rewrite_AdminhtmlCatalogProductEditTabInventory {} 
 }else{
    /* default extends start */
    class Aitoc_Aitquantitymanager_Block_Rewrite_AdminCatalogProductEditTabInventory_Aittmp extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Inventory {}
    /* default extends end */
}

/* AITOC static rewrite inserts end */
class Aitoc_Aitquantitymanager_Block_Rewrite_AdminCatalogProductEditTabInventory extends Aitoc_Aitquantitymanager_Block_Rewrite_AdminCatalogProductEditTabInventory_Aittmp
{
    // override parent        
    public function __construct()
    {
        parent::__construct();
#        $this->setTemplate('catalog/product/tab/inventory.phtml');
        $this->setTemplate('aitcommonfiles/design--adminhtml--default--default--template--catalog--product--tab--inventory.phtml'); // aitoc code
    }

    
// start aitoc code
    public function isDefaultWebsite()
    {
        $iWebsiteId = 0;
        
        if ($store = $this->getRequest()->getParam('store')) 
        {
            $iWebsiteId = Mage::app()->getStore($store)->getWebsiteId();
        }
        
        if (!$iWebsiteId) 
        {
            return true;
        }
        else 
        {
            return false;
        }
    }
// finish aitoc

}