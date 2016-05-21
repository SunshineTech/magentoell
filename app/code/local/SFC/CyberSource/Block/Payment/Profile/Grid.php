<?php
/**
 * StoreFront CyberSource Tokenized Payment Extension for Magento
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of StoreFront Consulting, Inc.
 *
 * @category  SFC
 * @package   SFC_CyberSource
 * @author    Garth Brantley <garth@storefrontconsulting.com>
 * @copyright 2009-2013 StoreFront Consulting, Inc. All Rights Reserved.
 * @license   http://www.storefrontconsulting.com/media/downloads/ExtensionLicense.pdf StoreFront Consulting Commercial License
 * @link      http://www.storefrontconsulting.com/cybersource-saved-credit-cards-extension-for-magento/
 *
 */

class SFC_CyberSource_Block_Payment_Profile_Grid extends Mage_Adminhtml_Block_Template
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

    public function getPaymentProfiles()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $collection = Mage::getModel('sfc_cybersource/payment_profile')->getCollection();
        $collection->addFieldToFilter('customer_id', $customer->getId());

        return $collection;
    }

}
