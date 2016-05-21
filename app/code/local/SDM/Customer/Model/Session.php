<?php
/**
 * Separation Degrees One
 *
 * Ellison's Mage_Sales customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Customer
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Customer_Model_Session class
 */
class SDM_Customer_Model_Session extends Mage_Customer_Model_Session
{
    /**
     * Get customer group ID. If customer is not logged in, the 'not logged in'
     * group ID is returned.
     *
     * Furtuermore, if a customer session has been expired but not explicitly
     * logged out, the 'not logged in' group ID is returned.
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        if ($this->getData('customer_group_id')) {
            if ($this->isLoggedIn()) {
                return $this->getData('customer_group_id');
            } else {
                return Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
            }
        }

        if ($this->isLoggedIn() && $this->getCustomer()) {
            return $this->getCustomer()->getGroupId();
        }

        return Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
    }
}
