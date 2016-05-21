<?php
/**
 * Separation Degrees One
 *
 * Manages the retailer application
 *
 * PHP Version 5.5
 *
 * @category  SDM
 * @package   SDM_RetailerApplication
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Retailer application class
 */
class SDM_RetailerApplication_Model_Application extends Mage_Core_Model_Abstract
{
    /**
     * Init resource model
     *
     * @return null
     */
    protected function _construct()
    {
        $this->_init('retailerapplication/application');
    }

    /**
     * Get's a specific address
     *
     * @param string $type
     *
     * @return Mage_Customer_Model_Address
     */
    public function getAddress($type)
    {
        $addressId = $this->getData($type . '_address_id');
        $address = Mage::getModel('customer/address')->load($addressId);

        // Address doesn't exist (not assigned yet or deleted)
        if (!$address->getId()) {
            // Clear invalid address ID
            $this->setData($type . '_address_id', null)->save();

            // Initialize an address
            $address->setParentId($this->getCustomerId());
            $address->setEntityTypeId(2);
        }

        return $address;
    }

    /**
     * Returns all the possible statuses
     *
     * @return array
     */
    public function getStatuses()
    {
        return Mage::helper('retailerapplication')->getStatuses();
    }

    /**
     * Returns the current application status (pending by default)
     *
     * @param bool $showLabel
     *
     * @return string
     */
    public function getStatus($showLabel = false)
    {
        $status = $this->getData('status');
        $status = empty($status) ? SDM_RetailerApplication_Helper_Data::STATUS_PENDING : $status;
        if ($showLabel) {
            $statuses = $this->getStatuses();
            return isset($statuses[$status]) ? $statuses[$status] : $status;
        } else {
            return $this->getData('status');
        }
    }

    /**
     * Returns the owner's address
     *
     * @return Mage_Customer_Model_Address
     */
    public function getOwnerAddress()
    {
        return $this->getAddress('owner');
    }

    /**
     * Returns the billing address
     *
     * @return Mage_Customer_Model_Address
     */
    public function getBillingAddress()
    {
        return $this->getAddress('billing');
    }

    /**
     * Returns the shipping address
     *
     * @return Mage_Customer_Model_Address
     */
    public function getShippingAddress()
    {
        return $this->getAddress('shipping');
    }

    /**
     * Loads the application for the current customer
     *
     * @return $this
     */
    public function loadCurrentCustomer()
    {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            if ($this->getCustomerId() === $session->getCustomer()->getId()) {
                return $this;
            }
            $application = $this->loadByCustomer($session->getCustomer());
            if (!$application->getId()
                && Mage::helper('sdm_core')->isSite(SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_RE)
            ) {
                // Initialize a new application
                $application->setCustomerId($session->getCustomer()->getId());
                $application->setStatus(SDM_RetailerApplication_Helper_Data::STATUS_PENDING);
                $application->save();
            }
            return $application;
        } else {
            return null;
        }
    }

    /**
     * Load a particular customer's application
     *
     * @param int|Mage_Customer_Model_Customer $customer
     *
     * @return $this
     */
    public function loadByCustomer($customer)
    {
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $customer = $customer->getId();
        }
        return $this->load($customer, 'customer_id');
    }
}
