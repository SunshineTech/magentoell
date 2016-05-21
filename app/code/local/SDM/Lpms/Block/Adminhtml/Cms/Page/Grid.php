<?php
/**
 * Separation Degrees Media
 *
 * Ellison's custom Landing Page Management System (LPMS).
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Lpms
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Lpms_Block_Adminhtml_Cms_Page_Grid class
 */
class SDM_Lpms_Block_Adminhtml_Cms_Page_Grid
    extends Mage_Adminhtml_Block_Cms_Page_Grid
{
    /**
     * Prepare columns
     *
     * @return SDM_Lpms_Block_Adminhtml_Cms_Page_Grid
     */
    protected function _prepareColumns()
    {

        $return = parent::_prepareColumns();

        $this->removeColumn('creation_time');
        $this->removeColumn('update_time');
        $this->removeColumn('page_actions');

        $this->addColumn('type', array(
            'header'    => Mage::helper('cms')->__('Type'),
            'index'     => 'type',
            'type'      => 'options',
            'options'   => Mage::helper('lpms')->getPageTypes()
        ));

        $this->addColumn('creation_time', array(
            'header'    => Mage::helper('cms')->__('Date Created'),
            'index'     => 'creation_time',
            'type'      => 'datetime',
        ));

        $this->addColumn('update_time', array(
            'header'    => Mage::helper('cms')->__('Last Modified'),
            'index'     => 'update_time',
            'type'      => 'datetime',
        ));

        $this->addColumn('page_actions', array(
            'header'    => Mage::helper('cms')->__('Action'),
            'width'     => 10,
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => 'adminhtml/cms_page_grid_renderer_action',
        ));

        return $return;
    }
}
