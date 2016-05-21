<?php
/**
 * Mexbs_Tieredcoupon_Block_Adminhtml_Tieredcoupon_Edit_Tab_Subcoupons
 * class that is used for displaying the tab of the sub coupons
 *
 * @copyright MexBS
 * @author MexBS <it@mexbs.com>
 */
class Mexbs_Tieredcoupon_Block_Adminhtml_Tieredcoupon_Edit_Tab_Subcoupons
    extends Mage_Adminhtml_Block_Text_List
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * getter for the tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('mexbs_tieredcoupon')->__('Manage Sub Coupons');
    }

    /**
     * getter for the tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('mexbs_tieredcoupon')->__('Manage Sub Coupons');
    }

    /**
     * gets whether the tab can be displayed
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * gets whether the tab is hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

}
