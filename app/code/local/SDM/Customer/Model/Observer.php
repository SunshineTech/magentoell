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
 * SDM_Customer_Model_Observer class
 */
class SDM_Customer_Model_Observer
{
    /**
     * Determines if a customer is new in a singleton observer
     *
     * @var boolean
     */
    protected $_isNewCustomerFlag = false;

    /**
     * Flag the customer new for setRetailCustomerGroup()
     *
     * @param Varien_Event_Observer $observer
     *
     * @return null
     */
    public function flagCustomer($observer)
    {
        $customer = $observer->getCustomer();
        // If 'new_customer_override' is set to true, the new customer flag is not set
        if (!$customer || $customer->getNewCustomerOverride() === true) {
            return;
        }

        // Set flag for setRetailCustomerGroup()
        if ($customer->getId()) {
            $this->_isNewCustomerFlag = false;
        } else {
            $this->_isNewCustomerFlag = true;
        }
    }

    /**
     * This is a fix for Amasty_Perm
     * @param object $observer
     * @return null
     */
    public function handleAdminUserSaveBefore($observer)
    {
        $user = $observer->getDataObject();
        $str = $user->getCustomerGroupId();
        if (!empty($str) && is_array($str)) {
            $user->setCustomerGroupId(implode(",", $str));
        } else {
            $user->setCustomerGroupId(null);
        }
    }

    /**
     * Set the default customer group for the retailer website
     *
     * @param Varien_Event_Observer $observer
     *
     * @return null
     */
    public function setRetailCustomerGroup($observer)
    {
        $customer = $observer->getCustomer();
        if (!$customer) {
            return;
        }

        $websiteCode = Mage::getModel('core/website')->load($customer->getWebsiteId())
            ->getCode();

        // Only for the retailer site
        if ($websiteCode == SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_RE) {
            if ($this->_isNewCustomerFlag) {
                $group = $this->_getDefaultRetailerCustomerGroup();
                $customer->setGroupId($group->getCustomerGroupId());
                $customer->save();
            }
        }
    }

    /**
     * Returns all of the customer groups
     *
     * @return array
     */
    protected function _getDefaultRetailerCustomerGroup()
    {
        $group = Mage::getModel('customer/group')->getCollection()
            ->addFieldToFilter('customer_group_code', SDM_Customer_Helper_Data::DEFAULT_RETAILER_GROUP_CODE)
            ->getFirstItem();

        return $group;
    }
}
