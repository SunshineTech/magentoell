<?php
/**
 * Separation Degrees One
 *
 * Banner Ads
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Banner
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */
/**
 * SDM_Banner_Block_Adminhtml_Slider_Edit class
 */
class SDM_Banner_Block_Adminhtml_Slider_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Initialize
     */
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'slider';
        $this->_controller = 'adminhtml_slider';
        $this->_updateButton('save', 'label', Mage::helper('slider')->__('Save Banner'));
        $this->_updateButton('delete', 'label', Mage::helper('slider')->__('Delete Banner'));
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
            ), -100);

        $this->_formScripts[] = "
        function toggleEditor() {
            if (tinyMCE.getInstanceById('slider_content') == null) {
                tinyMCE.execCommand('mceAddControl', false, 'slider_content');
            } else {
                tinyMCE.execCommand('mceRemoveControl', false, 'slider_content');
            }
        }

        function saveAndContinueEdit(){
            editForm.submit($('edit_form').action+'back/edit/');
        }
        ";
    }

    /**
     * Defaine header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('slider_data') && Mage::registry('slider_data')->getId()) {
            return Mage::helper('slider')->__(
                "Edit Banner '%s'",
                $this->htmlEscape(Mage::registry('slider_data')->getTitle())
            );
        } else {
            return Mage::helper('slider')->__('Add Banner');
        }
    }
}
