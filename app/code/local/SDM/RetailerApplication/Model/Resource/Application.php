<?php
/**
 * Separation Degrees One
 *
 * Manages the retailer application
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_RetailerApplication
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_RetailerApplication_Model_Resource_Application class
 */
class SDM_RetailerApplication_Model_Resource_Application
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize table and PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('retailerapplication/application', 'id');
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

        $object->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());

        $status = $object->getStatus();
        if (empty($status)) {
            $object->setStatus(SDM_RetailerApplication_Helper_Data::STATUS_PENDING);
        }

        return parent::_beforeSave($object);
    }
}
