<?php
/**
 * Separation Degrees Media
 *
 * This module handles all the store locator functionality for Ellison.
 *
 * The original code from this module is based off the FME_Gmapstrlocator module. We converted their
 * module to an SDM module rather than extending from it because the amount of modifications and
 * rewrites necessary for it to fit Ellison's spec were extensive, yet we still felt there was value
 * in using FME's module as a starting point.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Gmapstrlocator
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Gmapstrlocator_Model_Resource_Location class
 */
class SDM_Gmapstrlocator_Model_Resource_Location
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize
     *
     * @return void
     */
    public function _construct()
    {
        // Note that the gmapstrlocator_id refers to the key field in your database table.
        $this->_init('gmapstrlocator/gmapstrlocator_location', 'gmapstrlocator_id');
    }

    /**
     * Get website ids
     *
     * @param integer $id
     *
     * @return array
     */
    public function getWebsiteIdsByLocationId($id)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from(
                array('w' => $this->getTable('gmapstrlocator/gmapstrlocator_website')),
                array() // select none and do it in ->columns()
            )
            ->where('w.gmapstrlocator_id = ?', $id)
            ->columns(array('w.website_id'));

         $result = $adapter->fetchAll($select);

         return $result;
    }

    /**
     * After load logic
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return SDM_Gmapstrlocator_Model_Resource_Location
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        //This load the selected stores
        $select = $this->_getReadAdapter()->select()->from($this->getTable('gmapstrlocator_website'))
            ->where('gmapstrlocator_id = (?)', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $websiteArray = array();
            foreach ($data as $strInfo) {
                $websiteArray[] = $strInfo['website_id'];
            }
            $object->setData('website_id', $websiteArray);
        }

        $productLines = $object->getProductLines();
        if (!empty($productLines)) {
            $object->setProductLines(
                explode('|', trim($productLines, '| '))
            );
        }

        $storeType = $object->getStoreType();
        if (!empty($storeType)) {
            $object->setStoreType(
                explode('|', trim($storeType, '| '))
            );
        }

        $representativeServing = $object->getRepresentativeServing();
        if (!empty($representativeServing)) {
            $representativeServing = explode('|', trim($representativeServing, '| '));
            $object->setRepresentativeServing(
                implode(', ', $representativeServing)
            );
        }

        return parent::_afterLoad($object);
    }

    /**
     * Before save logic
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return SDM_Gmapstrlocator_Model_Resource_Location
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if ($object->isObjectNew() && $object->getCreatedTime() === null) {
            $object->setCreatedTime(Mage::getSingleton('core/date')->gmtDate());
        }
        $object->setUpdateTime(Mage::getSingleton('core/date')->gmtDate());

        $productLines = $object->getProductLines();
        if (is_array($productLines)) {
            $object->setProductLines(
                '|' . implode('|', $productLines) . '|'
            );
        }

        $storeType = $object->getStoreType();
        if (is_array($storeType)) {
            $object->setStoreType(
                '|' . implode('|', $storeType) . '|'
            );
        }

        $representativeServing = $object->getRepresentativeServing();
        if (!empty($representativeServing)) {
            $representativeServing = array_map('trim', explode(',', $representativeServing));
            $object->setRepresentativeServing(
                '|' . implode('|', $representativeServing) . '|'
            );
        }

        return parent::_beforeSave($object);
    }

    /**
     * After save logic
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return SDM_Gmapstrlocator_Model_Resource_Location
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        //This update the website table
        $condition = $this->_getWriteAdapter()->quoteInto('gmapstrlocator_id = ?', $object->getId());

        $this->_getWriteAdapter()->delete($this->getTable('gmapstrlocator_website'), $condition);
        foreach ((array)$object->getData('website_id') as $website) {
            $websiteArray = array();
            $websiteArray['gmapstrlocator_id'] = $object->getId();
            $websiteArray['website_id'] = $website;
            $this->_getWriteAdapter()->insert($this->getTable('gmapstrlocator_website'), $websiteArray);
        }

        return parent::_afterSave($object);
    }
}
