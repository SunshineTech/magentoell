<?php
/**
 * Mexbs_Tieredcoupon_Model_Tieredcoupon
 * class that is used for manipulating the grouping coupon through a model
 *
 * @copyright MexBS
 * @author MexBS <it@mexbs.com>
 *
 * @method array getSubCouponCodes()
 */
class Mexbs_Tieredcoupon_Model_Tieredcoupon extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('mexbs_tieredcoupon/tieredcoupon');
    }
}