<?php
/**
 * Mexbs_Tieredcoupon_Block_Adminhtml_Tieredcoupon_Edit_Tabs
 * class that is used for displaying the wrapper of the edit tabs
 *
 * @copyright MexBS
 * @author MexBS <it@mexbs.com>
 */
class Mexbs_Tieredcoupon_Block_Adminhtml_Tieredcoupon_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('tieredcoupon_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('mexbs_tieredcoupon')->__('Tiered Coupon'));
    }
}
