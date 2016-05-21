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
 * SDM_Calendar_Block_Adminhtml_System_Config_Form_Field_Event_Color class
 */
class SDM_Calendar_Block_Adminhtml_System_Config_Form_Field_Event_Color
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * Initialize
     */
    public function __construct()
    {
        $this->addColumn('color', array(
            'label' => Mage::helper('adminhtml')->__('Code'),
            'style' => 'width:120px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('sdm_calendar')->__('Add');
        parent::__construct();
    }
}
