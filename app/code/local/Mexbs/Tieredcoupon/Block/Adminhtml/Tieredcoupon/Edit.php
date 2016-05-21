<?php
/**
 * Mexbs_Tieredcoupon_Block_Adminhtml_Tieredcoupon_Edit
 * class that is used for displaying the wrapper of the edit page
 *
 * @copyright MexBS
 * @author MexBS <it@mexbs.com>
 */
class Mexbs_Tieredcoupon_Block_Adminhtml_Tieredcoupon_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    /**
     * initializes the form
     */
    public function __construct()
    {
        $this->_objectId = 'tieredcoupon_id';
        $this->_controller = 'adminhtml_tieredcoupon';
        $this->_blockGroup = 'mexbs_tieredcoupon';

        parent::__construct();

        $this->_addButton('save_and_continue_edit', array(
            'class'   => 'save',
            'label'   => Mage::helper('salesrule')->__('Save and Continue Edit'),
            'onclick' => 'editForm.submit($(\'edit_form\').action + \'back/edit/\')',
        ), 10);
    }

    /**
     * getter for form header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $tieredcoupon = Mage::registry('current_tieredcoupon');
        if ($tieredcoupon->getId()) {
            return Mage::helper('mexbs_tieredcoupon')->__("Edit Tiered Coupon '%s'", $this->escapeHtml($tieredcoupon->getName()));
        }
        else {
            return Mage::helper('mexbs_tieredcoupon')->__('New Tiered Coupon');
        }
    }
}