<?php
/**
 * Separation Degrees One
 *
 * Allows customers to follow an item
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_FollowItem
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_FollowItem_Model_Resource_Follow class
 */
class SDM_FollowItem_Model_Resource_Follow
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize table and PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('followitem/follow', 'id');
    }

    /**
     * Process page data before saving
     *
     * @param  Mage_Core_Model_Abstract $object
     * @return SDM_Lpms_Model_Resource_Asset
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if ($object->isObjectNew() && !$object->hasCreatedAt()) {
            $object->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
        }

        $object->setStoreId(
            Mage::app()->getStore()->getId()
        );

        $type = $object->getType();
        if (empty($type)) {
            $object->setType('product');
        }

        return parent::_beforeSave($object);
    }
}
