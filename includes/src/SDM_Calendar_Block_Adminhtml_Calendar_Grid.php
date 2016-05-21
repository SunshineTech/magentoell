<?php
/**
 * Separation Degrees One
 *
 * Ellison's Teachers' Planning Calendar
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Calendar
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Calendar grid
 */
class SDM_Calendar_Block_Adminhtml_Calendar_Grid
     extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('calendarGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir(Zend_Db_Select::SQL_DESC);
        $this->setSaveParametersInSession(true);
    }

    /**
     * Load collection
     *
     * @return SDM_Calendar_Block_Adminhtml_Calendar_Grid
     */
    protected function _prepareCollection()
    {
        $this->setCollection(
            Mage::getModel('sdm_calendar/calendar')->getCollection()
        );
        return parent::_prepareCollection();
    }

    /**
     * Create columns
     *
     * @return SDM_Calendar_Block_Adminhtml_Calendar_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => Mage::helper('sdm_calendar')->__('ID'),
            'align'  =>'right',
            'width'  => '50px',
            'type'   => 'number',
            'index'  => 'id',
        ));
        $this->addColumn('name', array(
            'header' => Mage::helper('sdm_calendar')->__('Name'),
            'index'  => 'name',
        ));
        $this->addColumn('type', array(
            'header'  => Mage::helper('sdm_calendar')->__('Type'),
            'type'    => 'options',
            'options' => Mage::getSingleton('sdm_calendar/adminhtml_system_config_source_calendar_type')
                ->toOptionArray(false),
            'index' => 'type',
        ));
        return parent::_prepareColumns();
    }

    /**
     * Get row link
     *
     * @param  SDM_Calendar_Model_Calendar $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl("*/*/edit", array('id' => $row->getId()));
    }
}
