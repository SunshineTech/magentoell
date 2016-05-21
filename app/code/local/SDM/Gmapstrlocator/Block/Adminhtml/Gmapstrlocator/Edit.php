<?php
/**
 * Separation Degrees Media
 *
 * This module handles all the store locator functionality for Ellison.
 *
 * The original code from this module is based off the FME_Gmapstrlocator module. We converted their
 * module to an SDM module rather than extending from it because the amount of modifications and
 * rewrites necessary for it to fit Ellison's spec were extensive, yet we still felt there was value
 * in using FME's module as a starting point.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Gmapstrlocator
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator_Edit class
 */
class SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Prepare layout
     *
     * @return SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator_Edit
     */
    protected function _prepareLayout()
    {
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled() && ($block = $this->getLayout()->getBlock('head'))) {
            $block->setCanLoadTinyMce(true);
        }
        return parent::_prepareLayout();
    }

    /**
     * Initialize
     */
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'gmapstrlocator';
        $this->_controller = 'adminhtml_gmapstrlocator';

        $this->_updateButton('save', 'label', Mage::helper('gmapstrlocator')->__('Save Store'));
        $this->_updateButton('delete', 'label', Mage::helper('gmapstrlocator')->__('Delete Store'));

        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
            ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('store_description') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'store_description');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'store_description');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * Header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('gmapstrlocator_data') && Mage::registry('gmapstrlocator_data')->getId()) {
            return Mage::helper('gmapstrlocator')->__(
                "Edit Store '%s'",
                $this->htmlEscape(Mage::registry('gmapstrlocator_data')->getStoreName())
            );
        } else {
            return Mage::helper('gmapstrlocator')->__('Add Store');
        }
    }
}
