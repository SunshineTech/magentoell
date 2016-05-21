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
 * Calendar grid container
 */
class SDM_Calendar_Block_Adminhtml_Calendar
     extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize grid
     */
    public function __construct()
    {
        $this->_controller     = 'adminhtml_calendar';
        $this->_blockGroup     = 'sdm_calendar';
        $this->_headerText     = Mage::helper('sdm_calendar')->__('Manage Calendars');
        $this->_addButtonLabel = Mage::helper('sdm_calendar')->__('Add Calendar');
        parent::__construct();
    }
}
