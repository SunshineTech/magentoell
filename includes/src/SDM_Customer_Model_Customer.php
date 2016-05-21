<?php
/**
 * Separation Degrees One
 *
 * Ellison's Mage_Customer customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Customer
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Customer_Model_Customer class
 */
class SDM_Customer_Model_Customer extends Mage_Customer_Model_Customer
{
    /**
     * Store a reference to our retailer application
     *
     * @var null
     */
    protected $_retailerApplication = null;

    /**
     * Alternate wording for getApplication()
     *
     * @return object
     */
    public function getRetailerApplication()
    {
        return $this->getApplication();
    }

    /**
     * Get's this user's application
     *
     * @return object
     */
    public function getApplication()
    {
        if ($this->_retailerApplication === null) {
            $this->_retailerApplication = Mage::helper('retailerapplication')
                ->getApplicationByCustomer($this->getId());
        }
        return $this->_retailerApplication;
    }

    /**
     * Checks if the user is logged in as an approved retailer
     *
     * @return boolean
     */
    public function isApprovedRetailer()
    {
        // Msut be logged in
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            return false;
        }

        // Check status
        return $this->getApplication()->getStatus() === SDM_RetailerApplication_Helper_Data::STATUS_APPROVED;
    }

    /**
     * Customer addresses collection
     *
     * Note: v0.2.1 fixes the EAV setup
     *
     * @return Mage_Customer_Model_Entity_Address_Collection
     */
    public function getAddressesCollection()
    {
        if ($this->_addressesCollection === null) {
            $this->_addressesCollection = $this->getAddressCollection()
                ->setCustomerFilter($this)
                ->addAttributeToSelect('*');

            // Remove uneditable addresses.
            if (!Mage::app()->getStore()->isAdmin()) {
                $this->_addressesCollection->addAttributeToFilter('is_editable', '1');  // v0.2.1
            }

            foreach ($this->_addressesCollection as $address) {
                $address->setCustomer($this);
            }
        }

        return $this->_addressesCollection;
    }

    /**
     * Validate customer attribute values for the password reset request. When
     * a password reset is requested, pre-condition of having first and last
     * names are not always. This is true for migrated customer from the MongoDB,
     * as many of the customers there did not have names.
     *
     * @see Mage_Customer_Model_Customer:validate()
     *
     * @return bool
     */
    public function validateForPasswordReset()
    {
        $errors = array();

        // Skip checking first and last names
        if (!Zend_Validate::is($this->getEmail(), 'EmailAddress')) {
            $errors[] = Mage::helper('customer')->__('Invalid email address "%s".', $this->getEmail());
        }

        $password = $this->getPassword();
        if (!$this->getId() && !Zend_Validate::is($password, 'NotEmpty')) {
            $errors[] = Mage::helper('customer')->__('The password cannot be empty.');
        }
        if (strlen($password) && !Zend_Validate::is($password, 'StringLength', array(6))) {
            $errors[] = Mage::helper('customer')->__('The minimum password length is %s', 6);
        }
        $confirmation = $this->getPasswordConfirmation();
        if ($password != $confirmation) {
            $errors[] = Mage::helper('customer')->__('Please make sure your passwords match.');
        }

        $entityType = Mage::getSingleton('eav/config')->getEntityType('customer');
        $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'dob');
        if ($attribute->getIsRequired() && '' == trim($this->getDob())) {
            $errors[] = Mage::helper('customer')->__('The Date of Birth is required.');
        }
        $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'taxvat');
        if ($attribute->getIsRequired() && '' == trim($this->getTaxvat())) {
            $errors[] = Mage::helper('customer')->__('The TAX/VAT number is required.');
        }
        $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'gender');
        if ($attribute->getIsRequired() && '' == trim($this->getGender())) {
            $errors[] = Mage::helper('customer')->__('Gender is required.');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }
}
