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
 * Edit calendar
 */
class SDM_Calendar_Block_Adminhtml_Calendar_Edit
     extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Initialize form
     */
    public function __construct()
    {
        parent::__construct();
        $this->_objectId   = 'id';
        $this->_blockGroup = 'sdm_calendar';
        $this->_controller = 'adminhtml_calendar';
        $this->_updateButton('save', 'label', Mage::helper('sdm_calendar')->__('Save Calendar'));
        $this->_updateButton('delete', 'label', Mage::helper('sdm_calendar')->__('Delete Calendar'));
        $this->_addButton('saveandcontinue', array(
            'label'   => Mage::helper('sdm_calendar')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class'   => 'save',
        ), -100);
        $this->_formScripts[] = <<<SCRIPT
function saveAndContinueEdit(){
    editForm.submit($('edit_form').action+'back/edit/');
}
SCRIPT;
    }

    /**
     * Add wysiwyg resources
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    /**
     * Define header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $calendar = Mage::registry('calendar_data');
        if ($calendar && $calendar->getId()) {
            return Mage::helper('sdm_calendar')
                ->__("Edit Calendar '%s'", $this->htmlEscape($calendar->getId()));

        } else {
            return Mage::helper('sdm_calendar')->__('Add Calendar');
        }
    }
}
