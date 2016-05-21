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
 * SDM_FollowItem_Model_Follow class
 */
class SDM_FollowItem_Model_Follow extends Mage_Core_Model_Abstract
{
    /**
     * A saved instance of this follow's entity
     *
     * @var object
     */
    protected $_entityInstance = null;

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('followitem/follow');
    }

    /**
     * Grabs the name for the associated entity
     *
     * @return string
     */
    public function getEntityName()
    {
        if ($this->getEntityInstance() === null) {
            return '';
        }
        return $this->getEntityInstance()->getName();
    }

    /**
     * Grabs the url for the associated entity
     *
     * @return string
     */
    public function getEntityUrl()
    {
        if ($this->getEntityInstance() === null) {
            return '';
        }
        switch ($this->getType()) {
            case 'product':
                return $this->getEntityInstance()->getProductUrl();
            case 'taxonomy':
                return DS . $this->getEntityInstance()->getType() . DS . $this->getEntityInstance()->getCode();
        }
    }

    /**
     * Grabs the prefix for the associated entity
     *
     * @return string
     */
    public function getEntityPrefix()
    {
        if ($this->getEntityInstance() === null) {
            return '';
        }
        switch ($this->getType()) {
            case 'product':
                return 'Product';
            case 'taxonomy':
                return ucwords($this->getEntityInstance()->getType());
        }
    }

    /**
     * Grabs the associated entity instance
     *
     * @return string
     */
    public function getEntityInstance()
    {
        if ($this->_entityInstance === null) {
            switch ($this->getType()) {
                case 'product':
                    $this->_entityInstance = Mage::getModel('catalog/product')->load($this->getEntityId());
                    break;
                case 'taxonomy':
                    $this->_entityInstance = Mage::getModel('taxonomy/item')->load($this->getEntityId());
                    break;
            }
        }
        return $this->_entityInstance;
    }

    /**
     * Set's the storeId and customerId
     *
     * @return $this
     */
    public function setBaseData()
    {
        $this->setStoreId(
            Mage::app()->getStore()->getId()
        );
        $this->setCustomerId(
            Mage::getSingleton('customer/session')->getCustomer()->getId()
        );
        return $this;
    }
}
