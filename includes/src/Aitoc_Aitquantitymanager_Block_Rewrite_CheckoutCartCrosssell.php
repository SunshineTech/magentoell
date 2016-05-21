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
/* $meta=%default,AdjustWare_Upsell% */
if(Mage::helper('core')->isModuleEnabled('AdjustWare_Upsell')){
    class Aitoc_Aitquantitymanager_Block_Rewrite_CheckoutCartCrosssell_Aittmp extends AdjustWare_Upsell_Block_Rewrite_FrontCheckoutCartCrosssell {} 
 }else{
    /* default extends start */
    class Aitoc_Aitquantitymanager_Block_Rewrite_CheckoutCartCrosssell_Aittmp extends Mage_Checkout_Block_Cart_Crosssell {}
    /* default extends end */
}

/* AITOC static rewrite inserts end */
class Aitoc_Aitquantitymanager_Block_Rewrite_CheckoutCartCrosssell extends Aitoc_Aitquantitymanager_Block_Rewrite_CheckoutCartCrosssell_Aittmp
{
    
    protected function _getCollection()
    {
        $collection = Mage::getModel('catalog/product_link')->useCrossSellLinks()
            ->getProductCollection()
            ->setStoreId(Mage::app()->getStore()->getId())
            ->addStoreFilter()
            ->setPageSize($this->_maxItemCount);
        $this->_addProductAttributesAndPrices($collection);

        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        //Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);

        return $collection;
    }
}