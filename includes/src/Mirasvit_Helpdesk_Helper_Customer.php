<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Help Desk MX
 * @version   1.1.3
 * @build     1578
 * @copyright Copyright (C) 2015 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Helpdesk_Helper_Customer extends Mage_Core_Helper_Abstract
{
    public function getCustomerByEmail(Mirasvit_Helpdesk_Model_Email $email)
    {
        $customers = Mage::getModel('customer/customer')->getCollection();
        $customers
            ->addAttributeToSelect('*')
            ->addFieldToFilter('email', $email->getFromEmail())
            ->addFieldToFilter('store_id', $email->getGateway()->getStoreId());
        if ($customers->count()) {
            return $customers->getFirstItem();
        }
        /** @var Mage_Customer_Model_Customer $address */
        $address = $customers->getLastItem();
        if ($address->getId()) {
            $customer = new Varien_Object();
            /* @noinspection PhpUndefinedMethodInspection */
            $customer->setName($address->getName());
            /* @noinspection PhpUndefinedMethodInspection */
            $customer->setEmail($address->getEmail());
            /* @noinspection PhpUndefinedMethodInspection */
            $customer->setQuoteAddressId($address->getId());

            return $customer;
        }
        $customer = new Varien_Object();
        if ($email->getSenderName() == '') {
            /* @noinspection PhpUndefinedMethodInspection */
            $customer->setName($email->getFromEmail());
        } else {
            /* @noinspection PhpUndefinedMethodInspection */
            $customer->setName($email->getSenderName());
        }
        /* @noinspection PhpUndefinedMethodInspection */
        $customer->setEmail($email->getFromEmail());

        return $customer;
    }

    /**
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer()
    {
        /* @noinspection PhpUndefinedMethodInspection */
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    public function getCustomerByPost($params)
    {
        $customer = $this->_getCustomer();
        // Patch for custom Contact Us form with ability to change email or name of customer (HDMX-98)
        if ($customer->getId() > 0 && !isset($params['customer_email']) && !isset($params['customer_name'])) {
            return $customer;
        }
        $email = $params['customer_email'];
        $name = $params['customer_name'];
        $customers = Mage::getModel('customer/customer')->getCollection();
        $customers
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('email', $email);
        if ($customers->count() > 0) {
            return $customers->getFirstItem();
        }
        /** @var Mage_Customer_Model_Customer $address */
        $address = $customers->getFirstItem();
        if ($address->getId()) {
            $customer = new Varien_Object();
            /* @noinspection PhpUndefinedMethodInspection */
            $customer->setName($address->getName());
            /* @noinspection PhpUndefinedMethodInspection */
            $customer->setEmail($address->getEmail());
            /* @noinspection PhpUndefinedMethodInspection */
            $customer->setQuoteAddressId($address->getId());

            return $customer;
        }
        $customer = new Varien_Object();
        /* @noinspection PhpUndefinedMethodInspection */
        $customer->setName($name);
        /* @noinspection PhpUndefinedMethodInspection */
        $customer->setEmail($email);

        return $customer;
    }
}
