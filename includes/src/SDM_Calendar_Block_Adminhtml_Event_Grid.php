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
 * Event grid
 */
class SDM_Calendar_Block_Adminhtml_Event_Grid
     extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('eventGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir(Zend_Db_Select::SQL_DESC);
        $this->setSaveParametersInSession(true);
    }

    /**
     * Load collection
     *
     * @return SDM_Calendar_Block_Adminhtml_Event_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('sdm_calendar/event')->getCollection();
        $collection->getSelect()->join(
            array('c' => $collection->getTable('sdm_calendar/calendar')),
            'c.id=main_table.calendar_id',
            array('calendar_name' => 'c.name')
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Create columns
     *
     * @return SDM_Calendar_Block_Adminhtml_Event_Grid
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
            'header'       => Mage::helper('sdm_calendar')->__('Name'),
            'index'        => 'name',
            'filter_index' => 'main_table.name'
        ));
        $this->addColumn('calendar_name', array(
            'header'  => Mage::helper('sdm_calendar')->__('Calendar'),
            'type'    => 'options',
            'options' => Mage::getResourceModel('sdm_calendar/calendar_collection')
                ->loadData()
                ->toOptionArray(),
            'index' => 'calendar_id',
            'filter_index' => 'c.id'
        ));
        $this->addColumn('start', array(
            'header' => Mage::helper('sdm_calendar')->__('Start'),
            'width'  => '100px',
            'type'   => 'date',
            'index'  => 'start',
        ));
        $this->addColumn('end', array(
            'header' => Mage::helper('sdm_calendar')->__('End'),
            'width'  => '100px',
            'type'   => 'date',
            'index'  => 'end',
        ));
        return parent::_prepareColumns();
    }

    /**
     * Get row link
     *
     * @param SDM_Calendar_Model_Event $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl("*/*/edit", array('id' => $row->getId()));
    }
}
