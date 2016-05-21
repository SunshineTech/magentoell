<?php
/**
 * Mexbs_Tieredcoupon_Model_Resource_Tieredcoupon
 * class that is used for connecting to the DB resource of grouping coupon
 *
 * @copyright MexBS
 * @author MexBS <it@mexbs.com>
 */
class Mexbs_Tieredcoupon_Model_Resource_Tieredcoupon extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('mexbs_tieredcoupon/tieredcoupon', 'tieredcoupon_id');
    }

    /**
     * Look up the sub coupon codes for given coupon id
     *
     * @param int $groupingCouponId
     * @return array
     */
    protected function _lookupSubCouponCodes($groupingCouponId)
    {
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
            ->from(array("salesrule_coupon" => $this->getTable('salesrule/coupon')), 'code')
            ->joinInner(array("tieredcoupon_coupon" => $this->getTable('mexbs_tieredcoupon/tieredcoupon_coupon')), "salesrule_coupon.coupon_id = tieredcoupon_coupon.coupon_id", array())
            ->where(sprintf("tieredcoupon_coupon.tieredcoupon_id = %s", $groupingCouponId));

        return $adapter->fetchCol($select);
    }

    /**
     * Perform operations after object load
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mexbs_Tieredcoupon_Model_Resource_Tieredcoupon
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId()) {
            $subCouponCodes = $this->_lookupSubCouponCodes($object->getId());
            $object->setData('sub_coupon_codes', $subCouponCodes);
        }

        return parent::_afterLoad($object);
    }

    /**
     * save the sub coupon codes for this model
     *
     * @param Mexbs_Tieredcoupon_Model_Tieredcoupon $object
     * @throws Exception
     */
    protected function _saveSubCouponCodes($object)
    {
        if(!is_array($object->getSubCouponCodes())){
            return;
        }

        $adapter = $this->_getWriteAdapter();


        $select  = $adapter->select()
            ->from(array("salesrule_coupon" => $this->getTable('salesrule/coupon')), 'coupon_id')
            ->where(sprintf("salesrule_coupon.code IN (%s)", '"'.implode('","',$object->getSubCouponCodes()).'"'));
        $couponIds = $adapter->fetchCol($select);

        if(!is_array($couponIds) || !count($couponIds)){
            return;
        }

        $adapter->beginTransaction();

        try{
            $adapter->delete(
                $this->getTable('mexbs_tieredcoupon/tieredcoupon_coupon'),
                sprintf("tieredcoupon_id=%s",$object->getId())
            );

            foreach($couponIds as $couponId){
                $adapter->insertOnDuplicate(
                    $this->getTable('mexbs_tieredcoupon/tieredcoupon_coupon'),
                    array(
                        "tieredcoupon_id" => $object->getId(),
                        "coupon_id"         => $couponId
                    ),
                    array("tieredcoupon_id", "coupon_id")
                );
            }
        }catch (Exception $e){
            $adapter->rollBack();
            throw new Exception();
        }

        $adapter->commit();
    }

    /**
     * Perform operations before object save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mexbs_Tieredcoupon_Model_Resource_Tieredcoupon
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $this->_saveSubCouponCodes($object);
        return parent::_afterSave($object);
    }
}