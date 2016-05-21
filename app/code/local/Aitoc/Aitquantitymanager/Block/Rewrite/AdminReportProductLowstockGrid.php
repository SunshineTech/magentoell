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


class Aitoc_Aitquantitymanager_Block_Rewrite_AdminReportProductLowstockGrid extends Mage_Adminhtml_Block_Report_Product_Lowstock_Grid
{
    // override parent
    protected function _prepareCollection()
    {
        if ($this->getRequest()->getParam('website')) {
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else if ($this->getRequest()->getParam('group')) {
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else if ($this->getRequest()->getParam('store')) {
            $storeId = (int)$this->getRequest()->getParam('store');
        } else {
            $storeId = '';
        }

//        $collection = Mage::getResourceModel('reports/product_lowstock_collection')
        $collection = Mage::getModel('aitquantitymanager/mysql4_product_lowstock_collection') // aitoc code
            ->addAttributeToSelect('*')
            ->setStoreId($storeId)
            ->filterByIsQtyProductTypes()
            ->joinInventoryItem('qty')
            ->joinInventoryItem('website_id')
            ->joinInventoryItem('use_default_website_stock')
            ->useManageStockFilter($storeId)
            ->useNotifyStockQtyFilter($storeId)
            ->setOrder('qty', 'asc');

        $resource = Mage::getSingleton('core/resource');
        
        // FIXED repeating products and lack of "qty" alias in Aitoc_Aitquantitymanager_Model_Mysql4_Product_Lowstock_Collection::_joinFields

        $collection
            ->getSelect()
            ->distinct();
        
        $collection->addToJoinFields('qty', array('table' => 'lowstock_inventory_item', 'field' => 'qty'));

        if( $storeId ) {
            $collection->addStoreFilter($storeId);
        }
        
        // start aitoc code
        
        if ($storeId)
        {
            $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        }
        else 
        {
            $websiteId = 0;
        }
        
        if (!$websiteId)
        {
            //use for all websites
            $hiddenWebsiteId = Mage::helper('aitquantitymanager')->getHiddenWebsiteId();
            $collection->getSelect()
                       ->columns(array(
                            //this is complicated key which will use instead default entity_id
                            'custom_entity_id' => 'CONCAT(`lowstock_inventory_item`.`website_id`,"-",e.entity_id)',
                            //when some website use default stock - we will not show this website
                            'website' => 'IF(`lowstock_inventory_item`.`use_default_website_stock` = 1, '.$hiddenWebsiteId.', `lowstock_inventory_item`.`website_id`)'
                       ))
                       ->join(array('w' => $resource->getTableName('core/website')),
                            'w.website_id = lowstock_inventory_item.website_id',
                            array('website_name' => 'GROUP_CONCAT(IF (w.name = "", "Default Stock", w.name) SEPARATOR ", ")'))
                       ->group(array('entity_id', 'website'));
            $collection->setIdFieldName('custom_entity_id');
        }
        else
        {
            $collection->getSelect()
                       ->columns(array(
                            'website' => 'lowstock_inventory_item.website_id'
                        ))
                       ->join(array('w' => $resource->getTableName('core/website')),
                            'w.website_id = lowstock_inventory_item.website_id',
                            array('website_name' => 'w.name')
                        );
        }
        
        // finish aitoc code
                 
        $this->setCollection($collection);
#        return parent::_prepareCollection();
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection(); // aitoc code
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('website', array(
            'header'    =>Mage::helper('reports')->__('Website Id'),
            'sortable'  =>false,
            'index'     =>'website',
            'width'     =>'50px'
        ));

        $this->addColumn('website_name', array(
            'header'    =>Mage::helper('reports')->__('Website Name'),
            'sortable'  =>false,
            'index'     =>'website_name'
        ));
        
        return parent::_prepareColumns();
    }
}