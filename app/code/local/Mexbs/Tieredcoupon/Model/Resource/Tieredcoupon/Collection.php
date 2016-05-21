<?php
/**
 * Mexbs_Tieredcoupon_Model_Resource_Tieredcoupon_Collection
 * class that is used for handling the grouping coupon collection
 *
 * @copyright MexBS
 * @author MexBS <it@mexbs.com>
 */
class Mexbs_Tieredcoupon_Model_Resource_Tieredcoupon_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('mexbs_tieredcoupon/tieredcoupon');
    }

    /**
     * adds the sub coupons to the select
     */
    public function addSubCouponsToSelect()
    {
       $this->getSelect()
           ->joinInner(array("tieredcoupon_coupon" => $this->getTable('mexbs_tieredcoupon/tieredcoupon_coupon')),
                        "main_table.tieredcoupon_id = tieredcoupon_coupon.tieredcoupon_id",
                        array())
           ->joinInner(array("coupon" => $this->getTable('salesrule/coupon')),
               "tieredcoupon_coupon.coupon_id = coupon.coupon_id",
               array("sub_coupon_codes" => new Zend_Db_Expr("group_concat(coupon.code separator ', ')")))
            ->group("tieredcoupon_coupon.tieredcoupon_id");
    }
}