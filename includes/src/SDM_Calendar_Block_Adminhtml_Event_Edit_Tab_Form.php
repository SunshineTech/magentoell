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
 * Edit event tabs
 */
class SDM_Calendar_Block_Adminhtml_Event_Edit_Tab_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form fields
     *
     * @return SDM_Calendar_Block_Adminhtml_Event_Edit_Tab_Form
     */
    protected function _prepareForm()
    {
        $calendarType = false;
        if (Mage::registry('event_data')) {
            $calendarId = Mage::registry('event_data')->getCalendarId();
            if ($calendarId) {
                $calendar = Mage::getModel('sdm_calendar/calendar')->load($calendarId);
                if ($calendar && $calendar->getId()) {
                    $calendarType = $calendar->getType();
                }
            }
        }
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'sdm_calendar_event_form',
            array(
                'legend' => $this->helper('sdm_calendar')->__('Event information')
            )
        );
        $fieldset->addField('name', 'text', array(
            'label'    => $this->helper('sdm_calendar')->__('Name'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'name',
        ));
        $fieldset->addField('calendar_id', 'select', array(
            'label'    => $this->helper('sdm_calendar')->__('Calendar'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'calendar_id',
            'options'  => Mage::getResourceModel('sdm_calendar/calendar_collection')
                ->loadData()
                ->toOptionArray(),
        ));
        $fieldset->addField('websites', 'multiselect', array(
            'label'    => Mage::helper('sdm_calendar')->__('Websites'),
            'class'    => 'required-entry',
            'required' => true,
            'values'   => Mage::getSingleton('sdm_calendar/adminhtml_system_config_source_website')->toOptionArray(),
            'name'     => 'websites',
        ));
        $fieldset->addField('start', 'date', array(
            'label'    => $this->helper('sdm_calendar')->__('Start Date'),
            'format'   => 'yyyy-MM-dd',
            'time'     => true,
            'image'    => $this->getSkinUrl('images/grid-cal.gif'),
            'class'    => 'required-entry validate-date-range date-range-event-start',
            'required' => true,
            'name'     => 'start',
        ));
        $fieldset->addField('end', 'date', array(
            'label'    => $this->helper('sdm_calendar')->__('End Date'),
            'format'   => 'yyyy-MM-dd',
            'time'     => true,
            'image'    => $this->getSkinUrl('images/grid-cal.gif'),
            'class'    => 'required-entry validate-date-range date-range-event-end',
            'required' => true,
            'name'     => 'end',
        ));
        if ($calendarType == SDM_Calendar_Model_Calendar::TYPE_GRID) {
            $this->_addGridFields($fieldset);
        } elseif ($calendarType == SDM_Calendar_Model_Calendar::TYPE_LIST) {
            $this->_addListFields($fieldset);
        }
        if (Mage::getSingleton('adminhtml/session')->getEventData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getEventData());
            Mage::getSingleton('adminhtml/session')->setEventData(null);
        } elseif (Mage::registry('event_data')) {
            $form->setValues(Mage::registry('event_data')->getData());
        }
        return parent::_prepareForm();
    }

    /**
     * Add fields for grid events
     *
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     *
     * @return void
     */
    protected function _addGridFields(Varien_Data_Form_Element_Fieldset &$fieldset)
    {
        $fieldset->addField('recurring', 'select', array(
            'label'    => $this->helper('sdm_calendar')->__('Recurring?'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'recurring',
            'default'  => SDM_Calendar_Model_Event::RECURRING_NONE,
            'options'  => Mage::getSingleton('sdm_calendar/adminhtml_system_config_source_recurring')
                ->toOptionArray(false),
        ));
        $fieldset->addType('event_color', 'SDM_Calendar_Block_Form_Element_Event_Color');
        $fieldset->addField('color', 'event_color', array(
            'label'  => $this->helper('sdm_calendar')->__('Color'),
            'name'   => 'color',
            'values' => Mage::getSingleton('sdm_calendar/adminhtml_system_config_source_event_color')
                ->toOptionArray(),
        ));
        $taxonomies =  Mage::getModel('taxonomy/item')
            ->getCollection()
            ->setOrder('type', Zend_Db_Select::SQL_ASC)
            ->setOrder('name', Zend_Db_Select::SQL_ASC);
        $options = array();
        foreach ($taxonomies as $option) {
            $options[$option->getId()] = sprintf('%s - %s', $option->getType(), $option->getName());
        }
        $fieldset->addField('taxonomy_id', 'select', array(
            'label'    => $this->helper('sdm_calendar')->__('Taxonomy'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'taxonomy_id',
            'options'  => $options,
        ));
    }

    /**
     * Add fields for list events
     *
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     *
     * @return void
     */
    protected function _addListFields(Varien_Data_Form_Element_Fieldset &$fieldset)
    {
        $fieldset->addField('desc', 'editor', [
            'label'   => $this->helper('sdm_calendar')->__('Description'),
            'name'    => 'desc',
            'wysiwyg' => true,
            'config'  => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
        ]);
        $fieldset->addField('sidebar', 'editor', [
            'label'   => $this->helper('sdm_calendar')->__('Sponsor'),
            'name'    => 'sidebar',
            'wysiwyg' => true,
            'config'  => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
        ]);
        $fieldset->addField('location', 'text', array(
            'label'    => $this->helper('sdm_calendar')->__('Location Name'),
            'name'     => 'location',
        ));
        $fieldset->addField('street', 'text', array(
            'label'    => $this->helper('sdm_calendar')->__('Street'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'street',
        ));
        $fieldset->addField('city', 'text', array(
            'label'    => $this->helper('sdm_calendar')->__('City'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'city',
        ));
        $fieldset->addField('state', 'text', array(
            'label'    => $this->helper('sdm_calendar')->__('State'),
            'name'     => 'state',
        ));
        $fieldset->addField('zip', 'text', array(
            'label'    => $this->helper('sdm_calendar')->__('Zip Code'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'zip',
        ));
        $fieldset->addField('country', 'text', array(
            'label'    => $this->helper('sdm_calendar')->__('Country'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'country',
        ));
        $fieldset->addField('image', 'image', array(
            'label'    => $this->helper('sdm_calendar')->__('Image'),
            'name'     => 'image',
            'note'     => 'Recommended dimensions: 450x450'
        ));
    }
}
