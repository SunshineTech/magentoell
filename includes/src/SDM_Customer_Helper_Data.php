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
 * SDM_Customer_Helper_Data class
 */
class SDM_Customer_Helper_Data extends SDM_Core_Helper_Data
{
    const DEFAULT_RETAILER_GROUP_CODE = 'Pending Retailer Account';
    const XML_PATH_MIN_ORDER_AMOUNT = 'sdm_customer/min_order/amount';
    const XML_PATH_MIN_FIRST_ORDER_AMOUNT = 'sdm_customer/min_order/first_amount';

    /**
     * Checks if the customer has placed orders previously
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param Mage_Core_Model_Store        $store
     *
     * @return bool
     */
    public function hasOrdered($customer, $store)
    {
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('store_id', $store->getId())
            ->addFieldToFilter('customer_id', $customer->getId());

        $collection->getSelect()->where('state != ?', Mage_Sales_Model_Order::STATE_CANCELED);

        if ($collection->count() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Returns the minimum order amount. If argument is provided, get the
     * customer's minimum amount.
     *
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return int
     */
    public function getMinOrderAmount($customer = null)
    {
        $minAmount = 0;
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $minAmount = $customer->getMinOrderAmount();
        }

        if ($minAmount <= 0) {
            $minAmount = Mage::getStoreConfig(self::XML_PATH_MIN_ORDER_AMOUNT);
        }

        return $minAmount;
    }

    /**
     * Returns the minimum first order amount. If argument is provided, get the
     * customer's minimum amount.
     *
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return int
     */
    public function getMinFirstOrderAmount($customer = null)
    {
        $minAmount = 0;
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $minAmount = $customer->getMinFirstOrderAmount();
        }

        if ($minAmount <= 0) {
            $minAmount = Mage::getStoreConfig(self::XML_PATH_MIN_FIRST_ORDER_AMOUNT);
        }

        return $minAmount;
    }
}
