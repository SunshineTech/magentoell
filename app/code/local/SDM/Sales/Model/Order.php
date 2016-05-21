<?php
/**
 * Separation Degrees Media
 *
 * Ellison's Mage_Sales customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Sales
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Sales_Model_Order class
 */
class SDM_Sales_Model_Order extends Mage_Sales_Model_Order
{
    /**
     * Returns the order customer's AX account ID (ax_customer_id attribute)
     *
     * @return int
     */
    public function getAxAccountId()
    {
        $customer = Mage::getModel('customer/customer')->load($this->getCustomerId());

        return $customer->getAxCustomerId();
    }

    /**
     * Returns the order's invoice AX account ID
     *
     * @return int
     */
    public function getInvoiceAccountId()
    {
        $customer = Mage::getModel('customer/customer')->load($this->getCustomerId());

        return $customer->getAxInvoiceId();
    }
}
