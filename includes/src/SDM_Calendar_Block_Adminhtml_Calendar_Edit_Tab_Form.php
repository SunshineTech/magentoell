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
class SDM_Calendar_Block_Adminhtml_Calendar_Edit_Tab_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form fields
     *
     * @return SDM_Calendar_Block_Adminhtml_Calendar_Edit_Tab_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'sdm_calendar_calendar_form',
            array(
                'legend' => Mage::helper('sdm_calendar')->__('Calendar information')
            )
        );
        $fieldset->addField('name', 'text', array(
            'label'    => Mage::helper('sdm_calendar')->__('Name'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'name',
        ));
        $fieldset->addField('url', 'text', array(
            'label'    => Mage::helper('sdm_calendar')->__('URL'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'url',
        ));
        $fieldset->addField('websites', 'multiselect', array(
            'label'    => Mage::helper('sdm_calendar')->__('Websites'),
            'class'    => 'required-entry',
            'required' => true,
            'values'   => Mage::getSingleton('sdm_calendar/adminhtml_system_config_source_website')->toOptionArray(),
            'name'     => 'websites',
        ));
        $fieldset->addField('type', 'select', array(
            'label'    => Mage::helper('sdm_calendar')->__('Type'),
            'class'    => 'required-entry',
            'required' => true,
            'options'  => Mage::getSingleton('sdm_calendar/adminhtml_system_config_source_calendar_type')
                ->toOptionArray(false),
            'name'     => 'type',
        ));
        $fieldset->addField('desc', 'editor', [
            'label'   => $this->helper('sdm_calendar')->__('Description'),
            'name'    => 'desc',
            'wysiwyg' => true,
            'config'  => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
            'note'    => "If uploading a banner image for a list type calendar, the recommended size is 1000x250"
        ]);
        if (Mage::getSingleton('adminhtml/session')->getCalendarData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getCalendarData());
            Mage::getSingleton('adminhtml/session')->setCalendarData(null);
        } elseif (Mage::registry('calendar_data')) {
            $form->setValues(Mage::registry('calendar_data')->getData());
        }
        return parent::_prepareForm();
    }
}
