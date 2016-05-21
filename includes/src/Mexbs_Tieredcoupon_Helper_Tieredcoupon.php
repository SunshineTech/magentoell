<?php
/**
 * Mexbs_Tieredcoupon_Helper_Tieredcoupon
 * class that is used for the grouped coupon feature
 *
 * @copyright MexBS
 * @author MexBS <it@mexbs.com>
 */
class Mexbs_Tieredcoupon_Helper_Tieredcoupon extends Mage_Core_Helper_Data
{
    /**
     * Gets whether the given coupon code represents grouped coupon
     *
     * @param string $couponCode
     * @return bool
     */
    public function getIsTieredCoupon($couponCode)
    {
        /**
         * @var Mexbs_Tieredcoupon_Model_Tieredcoupon $groupingCoupon
         */
        $groupingCoupon = Mage::getModel("mexbs_tieredcoupon/tieredcoupon")->load($couponCode, 'code');
        return (!is_null($groupingCoupon->getId()));
    }
}