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


class SFC_CyberSource_Model_Payment_Profile extends Mage_Core_Model_Abstract
{
    /**
     * Construct
     */
    protected function _construct()
    {
        $this->_init('sfc_cybersource/payment_profile');
    }

    /**
     * Init profile with customer data from customer record
     */
    public function initProfileWithCustomerDefault($customerId)
    {
        // Load customer
        /** @var Mage_Customer_Model_Customer $model */
        $model = Mage::getModel('customer/customer')->load($customerId);
        // Set basic customer data fields
        $this->setData('customer_id', $customerId);
        $this->setData('customer_fname', $model->getData('firstname'));
        $this->setData('customer_lname', $model->getData('lastname'));
        $this->setData('email', $model->getData('email'));
        // Grab default billing addy id
        $addressId = $model->getData('default_billing');
        // Add address data if default billing addy exists
        if ($addressId) {
            // Get address
            $address = Mage::getModel('customer/address')->load($addressId);
            // Set address
            $this->setBillingAddressFields($address);
        }
    }

    /**
     * Set billing address fields on payment profile from a Magento customer address
     * @param Mage_Customer_Model_Address_Abstract $billingAddress
     */
    public function setBillingAddressFields(Mage_Customer_Model_Address_Abstract $billingAddress)
    {
        $this->setData('firstname', $billingAddress->getData('firstname'));
        $this->setData('customer_fname', $billingAddress->getData('firstname'));
        $this->setData('lastname', $billingAddress->getData('lastname'));
        $this->setData('customer_lname', $billingAddress->getData('lastname'));
        $this->setData('company', $billingAddress->getData('company'));
        $this->setData('street1', $billingAddress->getStreet(1));
        $this->setData('street2', $billingAddress->getStreet(2));
        $this->setData('city', $billingAddress->getData('city'));
        $this->setData('region', $billingAddress->getRegionCode());
        $this->setData('postcode', $billingAddress->getData('postcode'));
        $this->setData('country_id', $billingAddress->getData('country_id'));
        $this->setData('telephone', $billingAddress->getData('telephone'));
        $this->setData('fax', $billingAddress->getData('fax'));
    }

    public function setProfileDataFromSOPPostBack($postData)
    {
        $sopPostBackFieldMap = array(
            'req_bill_to_forename' => 'customer_fname',
            'req_bill_to_surname' => 'customer_lname',
            'req_bill_to_address_line1' => 'street1',
            'req_bill_to_address_line2' => 'street2',
            'req_bill_to_address_city' => 'city',
            'req_bill_to_address_state' => 'region',
            'req_bill_to_address_postal_code' => 'postcode',
            'req_bill_to_address_country' => 'country_id',
            'req_bill_to_phone' => 'telephone',
            'req_bill_to_email' => 'email',
        );
        foreach($sopPostBackFieldMap as $sopField => $modelField) {
            if (isset($postData[$sopField])) {
                $this->setData($modelField, $postData[$sopField]);
            }
        }

        // Map cc num
        if (isset($postData['req_card_number'])) {
            $this->setData('customer_cardnumber', substr($postData['req_card_number'], -8));
        }
        if (isset($postData['req_card_expiry_date'])) {
            $this->setData('cc_exp_month', substr($postData['req_card_expiry_date'], 0, 2));
            $this->setData('cc_exp_year', substr($postData['req_card_expiry_date'], 3, 4));
        }
        if (isset($postData['req_card_type'])) {
            $this->setData('cc_type', $postData['req_card_type']);
        }
        // Map token
        if (isset($postData['payment_token'])) {
            $this->setData('payment_token', $postData['payment_token']);
        }
    }

    /**
     * Retrieve extra data fields for payment profile from gateway and set them on model object
     */
    public function retrieveProfileData()
    {
        // Lookup customer in DB
        /** @var Mage_Customer_Model_Customer $model */
        $customer = Mage::getModel('customer/customer')->load($this->getData('customer_id'));
        // Get payment profile id
        $paymentToken = $this->getData('payment_token');
        /** @var SFC_CyberSource_Helper_Gateway $gatewayHelper */
        $gatewayHelper = Mage::helper('sfc_cybersource/gateway');
        $gatewayHelper->setConfigWebsite($customer->getData('website_id'));
        // Call out to gateway API to retrieve profile data
        $data = $gatewayHelper->retrievePaymentProfileAsData($this->getData('customer_id'), $paymentToken);
        // Now inject the saved profile data into this model object
        $this->addData($data);

        // Re-mask the card number
        $this->setData('customer_cardnumber',
            SFC_CyberSource_Helper_Gateway::CC_MASK . substr($this->getData('customer_cardnumber'), -4, 4));
    }

    /**
     * Save payment profile to CyberSource via API
     *
     */
    public function saveProfileData()
    {
        // Lookup customer in DB
        /** @var Mage_Customer_Model_Customer $model */
        $customer = Mage::getModel('customer/customer')->load($this->getData('customer_id'));
        // Get payment profile id
        $paymentToken = $this->getData('payment_token');
        /** @var SFC_CyberSource_Helper_Gateway $gatewayHelper */
        $gatewayHelper = Mage::helper('sfc_cybersource/gateway');
        $gatewayHelper->setConfigWebsite($customer->getData('website_id'));
        // Set IP address on customer subscription
        // Set the IP address to that retrieved from mage core helper getRemoteAddr()
        $this->setData('ip_address', Mage::helper('core/http')->getRemoteAddr());
        // If there is a payment_token, then assume there is an existing record in gateway
        if (strlen($paymentToken) > 0) {
            // Save existing payment profile
            $gatewayHelper->updatePaymentProfileFromData($paymentToken, $this->getData());
        }
        else {
            // Call API
            $paymentToken = $gatewayHelper->createPaymentProfileFromData($this->getData());
        }

        // Now save payment profile id to model if it doesn't already exist
        if (strlen($paymentToken)) {
            $this->setData('payment_token', $paymentToken);
        }

        // Re-mask the card number
        $this->setData('customer_cardnumber',
            SFC_CyberSource_Helper_Gateway::CC_MASK . substr($this->getData('customer_cardnumber'), -4, 4));

    }

    /**
     * Delete the payment profile from CyberSource
     */
    public function deleteProfile()
    {
        // Lookup customer in DB
        /** @var Mage_Customer_Model_Customer $model */
        $customer = Mage::getModel('customer/customer')->load($this->getData('customer_id'));
        // Get payment profile id
        $paymentToken = $this->getData('payment_token');
        /** @var SFC_CyberSource_Helper_Gateway $gatewayHelper */
        $gatewayHelper = Mage::helper('sfc_cybersource/gateway');
        $gatewayHelper->setConfigWebsite($customer->getData('website_id'));
        // If there is a payment_token, then assume there is an existing record in gateway
        if (strlen($paymentToken) > 0) {
            // Delete existing payment profile
            $gatewayHelper->deletePaymentProfile($this->getData('customer_id'), $paymentToken);
        }
        // Now reset profile id in this model object
        $this->setData('payment_token', null);
    }

}
