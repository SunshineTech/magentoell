<?php
/**
 * Separation Degrees One
 *
 * Lyris Newsletter Management
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Lyris
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Edit Ads
 */
class SDM_Lyris_Block_Adminhtml_Ads_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Initialize form
     */
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'sdm_lyris';
        $this->_controller = 'adminhtml_ads';
        $this->_updateButton('save', 'label', Mage::helper('sdm_lyris')->__('Save Thumbnail'));
        $this->_updateButton('delete', 'label', Mage::helper('sdm_lyris')->__('Delete Thumbnail'));
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
            ), -100);

        $this->_formScripts[] = <<<SCRIPT
function saveAndContinueEdit(){
    editForm.submit($('edit_form').action+'back/edit/');
}
SCRIPT;
    }

    /**
     * Define header text
     * @return string
     */
    public function getHeaderText()
    {
        $ads = Mage::registry('ads_data');
        if ($ads && $ads->getId()) {
            return Mage::helper('sdm_lyris')
                ->__("Edit Banner '%s'", $this->htmlEscape($ads->getTitle()));
        } else {
            return Mage::helper('sdm_lyris')->__('Add Thumbnail');
        }
    }
}
