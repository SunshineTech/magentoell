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
/* AITOC static rewrite inserts start */
/* $meta=%default,Aitoc_Aitpermissions% */
if(Mage::helper('core')->isModuleEnabled('Aitoc_Aitpermissions')){
    class Aitoc_Aitquantitymanager_Block_Rewrite_AdminCatalogProductGrid_Aittmp extends Aitoc_Aitpermissions_Block_Rewrite_AdminCatalogProductGrid {} 
 }else{
    /* default extends start */
    class Aitoc_Aitquantitymanager_Block_Rewrite_AdminCatalogProductGrid_Aittmp extends Mage_Adminhtml_Block_Catalog_Product_Grid {}
    /* default extends end */
}

/* AITOC static rewrite inserts end */
class Aitoc_Aitquantitymanager_Block_Rewrite_AdminCatalogProductGrid extends Aitoc_Aitquantitymanager_Block_Rewrite_AdminCatalogProductGrid_Aittmp
{
    // override parent
    protected function _prepareCollection()
    {
        /* <<< AITOC CODE */
        $store = $this->_getStore(); 
        $iWebsiteId = $store->getWebsiteId(); 
        /* >>> AITOC CODE END */
        if (!$iWebsiteId)
        {
            $iWebsiteId = Mage::helper('aitquantitymanager')->getHiddenWebsiteId();
        }
        
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->joinField('qty',
//              'cataloginventory/stock_staus', // +++ AITOC comment
                'aitquantitymanager/stock_item', // +++AITOC code
//              'aitquantitymanager/stock_status', // +++AITOC comment
                'qty',
                'product_id=entity_id',
//              '{{table}}.stock_id=1',// +++AITOC comment
                '{{table}}.stock_id=1 AND {{table}}.website_id = ' . $iWebsiteId, // +++AITOC code
                'left');
        if ($store->getId()) {
//          $collection->setStoreId($store->getId());  // +++AITOC comment
            $collection->addStoreFilter($store);
            $collection->joinAttribute('custom_name', 'catalog_product/name', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $store->getId());
        }
        else {
            $collection->addAttributeToSelect('price');
            if(version_compare(Mage::getVersion(), '1.4.1.1', '<'))
            {
                $collection->addAttributeToSelect('status');
                $collection->addAttributeToSelect('visibility');
            }
            else
            {
                $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
                $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
            }
        }

        $this->setCollection($collection);

        Mage_Adminhtml_Block_Widget_Grid::_prepareCollection(); // +++AITOC code
        $this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }
}