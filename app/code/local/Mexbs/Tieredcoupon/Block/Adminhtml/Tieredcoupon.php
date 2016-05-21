<?php
/**
 * Mexbs_Tieredcoupon_Block_Adminhtml_Tieredcoupon
 * class that is used for displaying the grouping coupon grid in the backend
 *
 * @copyright MexBS
 * @author MexBS <it@mexbs.com>
 */
class Mexbs_Tieredcoupon_Block_Adminhtml_Tieredcoupon extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * constructor
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_tieredcoupon';
        $this->_blockGroup = 'mexbs_tieredcoupon';
        $this->_headerText = Mage::helper('mexbs_tieredcoupon')->__('Tiered Coupons');
        $this->_addButtonLabel = Mage::helper('mexbs_tieredcoupon')->__('Create New Tiered Coupon');
        parent::__construct();
    }

    /**
     * gets the url of creation of new grouping coupon
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/create');
    }
}