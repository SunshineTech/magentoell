<?php
/**
 * Mexbs_Tieredcoupon_Model_SalesRule_Validator
 * class that is used for overriding the core validator
 *
 * @copyright MexBS
 * @author MexBS <it@mexbs.com>
 *
 */
class Mexbs_Tieredcoupon_Model_SalesRule_Validator extends Mage_SalesRule_Model_Validator
{
    /**
     * Init validator
     * Init process load collection of rules for specific website,
     * customer group and coupon code
     *
     * @param   int $websiteId
     * @param   int $customerGroupId
     * @param   string $couponCode
     * @return  Mage_SalesRule_Model_Validator
     */
    public function init($websiteId, $customerGroupId, $couponCode)
    {
        $this->setWebsiteId($websiteId)
            ->setCustomerGroupId($customerGroupId)
            ->setCouponCode($couponCode);

        /**
         * @var Mexbs_Tieredcoupon_Helper_Tieredcoupon $tieredCouponHelper
         */
        $tieredCouponHelper = Mage::helper("mexbs_tieredcoupon/tieredcoupon");
        if($tieredCouponHelper->getIsTieredCoupon($couponCode)){
            $this->setIsTieredCoupon(true);
        }


        $key = $websiteId . '_' . $customerGroupId . '_' . $couponCode;
        if (!isset($this->_rules[$key])) {
            $this->_rules[$key] = Mage::getResourceModel('salesrule/rule_collection')
                ->setValidationFilter($websiteId, $customerGroupId, $couponCode, null, $this->getIsTieredCoupon())
                ->load();
        }
        return $this;
    }
}