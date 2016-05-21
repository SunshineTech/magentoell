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
/* $meta=%default,Aitoc_Aitcbp% */
if(Mage::helper('core')->isModuleEnabled('Aitoc_Aitcbp')){
    class Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogProductIndexerPrice_Aittmp extends Aitoc_Aitcbp_Model_Product_Indexer_Price {} 
 }else{
    /* default extends start */
    class Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogProductIndexerPrice_Aittmp extends Mage_Catalog_Model_Product_Indexer_Price {}
    /* default extends end */
}

/* AITOC static rewrite inserts end */
class Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogProductIndexerPrice extends Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogProductIndexerPrice_Aittmp
{
    // overide parent
    protected function _construct()
    {
//        $this->_init('catalog/product_indexer_price');
        $this->_init('aitquantitymanager/frontCatalogResourceEavMysql4ProductIndexerPrice');
    }
}