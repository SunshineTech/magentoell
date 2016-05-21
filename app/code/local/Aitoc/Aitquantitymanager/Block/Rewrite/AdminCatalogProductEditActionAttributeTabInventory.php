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
/* $meta=%default,Aitoc_Aitpermissions% */
if(Mage::helper('core')->isModuleEnabled('Aitoc_Aitpermissions')){
    class Aitoc_Aitquantitymanager_Block_Rewrite_AdminCatalogProductEditActionAttributeTabInventory_Aittmp extends Aitoc_Aitpermissions_Block_Rewrite_AdminhtmlCatalogProductEditActionAttributeTabInventory {} 
 }else{
    /* default extends start */
    class Aitoc_Aitquantitymanager_Block_Rewrite_AdminCatalogProductEditActionAttributeTabInventory_Aittmp extends Mage_Adminhtml_Block_Catalog_Product_Edit_Action_Attribute_Tab_Inventory {}
    /* default extends end */
}

/* AITOC static rewrite inserts end */
class Aitoc_Aitquantitymanager_Block_Rewrite_AdminCatalogProductEditActionAttributeTabInventory extends Aitoc_Aitquantitymanager_Block_Rewrite_AdminCatalogProductEditActionAttributeTabInventory_Aittmp
{
    
// start aitoc
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