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
 * Edit calendar tabs
 */
class SDM_Calendar_Block_Adminhtml_Calendar_Edit_Tabs
    extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Setup tabs
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('calendar_tabs')
            ->setDestElementId('edit_form')
            ->setTitle(Mage::helper('sdm_calendar')->__('Calendar Information'));
    }

    /**
     * Create tab
     *
     * @return SDM_Calendar_Block_Adminhtml_Calendar_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'   => Mage::helper('sdm_calendar')->__('Calendar Information'),
            'title'   => Mage::helper('sdm_calendar')->__('Calendar Information'),
            'content' => $this->getLayout()->createBlock('sdm_calendar/adminhtml_calendar_edit_tab_form')->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}
