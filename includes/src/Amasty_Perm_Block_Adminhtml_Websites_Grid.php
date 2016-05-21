<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */
class Amasty_Perm_Block_Adminhtml_Websites_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('amperm');
        $this->setDefaultSort('entity_id');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('core/website')->getCollection();

        $this->setCollection($collection);
        parent::_prepareCollection();

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('website_title', array(
            'header'   => Mage::helper('amperm')->__('Website Name'),
            'align'    =>'left',
            'index'    => 'name',
            'renderer' => 'amperm/adminhtml_websites_grid_renderer_website',
            'filter_index' => 'main_table.name',
        ));

        return parent::_prepareColumns();
    }
}