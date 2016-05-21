<?php
/**
 * Mexbs_Tieredcoupon_Block_Adminhtml_Tieredcoupon_Edit_Form
 * class that is used for displaying the edit form of the grouping coupon
 *
 * @copyright MexBS
 * @author MexBS <it@mexbs.com>
 */
class Mexbs_Tieredcoupon_Block_Adminhtml_Tieredcoupon_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('tieredcoupon_form');
        $this->setTitle(Mage::helper('mexbs_tieredcoupon')->__('Tiered Coupon Information'));
    }

    /**
     * prepares the form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => Mage::getUrl("*/*/save"), 'method' => 'post'));
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }


}
