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
 * Event grid container
 */
class SDM_Calendar_Block_Adminhtml_Event
     extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize grid
     */
    public function __construct()
    {
        $this->_controller     = 'adminhtml_event';
        $this->_blockGroup     = 'sdm_calendar';
        $this->_headerText     = Mage::helper('sdm_calendar')->__('Manage Events');
        $this->_addButtonLabel = Mage::helper('sdm_calendar')->__('Add Event');
        parent::__construct();
    }
}
