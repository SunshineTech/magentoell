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

class SFC_CyberSource_Block_Adminhtml_Customer_Paymentprofiles_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare edit form
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        // Create form
        $form = new Varien_Data_Form();
        // Create fieldset
        $fieldset =
            $form->addFieldset('sfc_cybersource_form',
                array('legend' => Mage::helper('sfc_cybersource')->__('Saved Credit Card Details')));

        // customer id
        $fieldset->addField('customer_id', 'hidden', array(
            'label' => Mage::helper('sfc_cybersource')->__('Customer ID'),
            'class' => '',
            'required' => false,
            'name' => 'customer_id',
        ));

        // email
        $fieldset->addField('email', 'hidden', array(
            'label' => Mage::helper('sfc_cybersource')->__('Email'),
            'class' => '',
            'required' => false,
            'name' => 'email',
        ));

        // CyberSource Payment Token
        $fieldset->addField('payment_token', 'hidden', array(
            'label' => Mage::helper('sfc_cybersource')->__('CyberSource Payment Token'),
            'class' => '',
            'required' => false,
            'name' => 'payment_token',
        ));

        // customer id
        $fieldset->addField('customer_id_readonly', 'label', array(
            'label' => Mage::helper('sfc_cybersource')->__('Customer ID'),
            'class' => '',
            'required' => false,
            'readonly' => true,
            'name' => 'customer_id_readonly',
        ));

        // email
        $fieldset->addField('email_readonly', 'label', array(
            'label' => Mage::helper('sfc_cybersource')->__('Email'),
            'class' => '',
            'required' => false,
            'readonly' => true,
            'name' => 'email_readonly',
        ));

        // CyberSource Payment Token
        $fieldset->addField('payment_token_readonly', 'label', array(
            'label' => Mage::helper('sfc_cybersource')->__('CyberSource Payment Token (Subscription ID)'),
            'class' => '',
            'required' => false,
            'readonly' => true,
            'name' => 'payment_token_readonly',
        ));

        // customer first name
        $fieldset->addField('customer_fname', 'text', array(
            'label' => Mage::helper('sfc_cybersource')->__('First Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'customer_fname',
        ));

        // customer last name
        $fieldset->addField('customer_lname', 'text', array(
            'label' => Mage::helper('sfc_cybersource')->__('Last Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'customer_lname',
        ));

        // street1
        $fieldset->addField('street1', 'text', array(
            'label' => Mage::helper('sfc_cybersource')->__('Street Address 1'),
            'class' => '',
            'required' => false,
            'name' => 'street1',
        ));

        // street2
        $fieldset->addField('street2', 'text', array(
            'label' => Mage::helper('sfc_cybersource')->__('Street Address 2'),
            'class' => '',
            'required' => false,
            'name' => 'street2',
        ));

        // country_id
        $fieldset->addField('country_id', 'select', array(
            'label' => Mage::helper('sfc_cybersource')->__('Country'),
            'class' => '',
            'required' => false,
            'values' => Mage::getModel('adminhtml/system_config_source_country')->toOptionArray(),
            'name' => 'country_id',
        ));

        // city
        $fieldset->addField('city', 'text', array(
            'label' => Mage::helper('sfc_cybersource')->__('City'),
            'class' => '',
            'required' => false,
            'name' => 'city',
        ));

        // region
        $fieldset->addField('region', 'text', array(
            'label' => Mage::helper('sfc_cybersource')->__('State'),
            'class' => '',
            'required' => false,
            'name' => 'region',
        ));

        // postcode
        $fieldset->addField('postcode', 'text', array(
            'label' => Mage::helper('sfc_cybersource')->__('Zip/Postal Code'),
            'class' => '',
            'required' => false,
            'name' => 'postcode',
        ));

        // phone number
        $fieldset->addField('telephone', 'text', array(
            'label' => Mage::helper('sfc_cybersource')->__('Phone Number'),
            'class' => '',
            'required' => false,
            'name' => 'telephone',
        ));

        // card type
        $fieldset->addField('cc_type', 'select', array(
            'label' => Mage::helper('sfc_cybersource')->__('Card Type'),
            'class' => '',
            'required' => false,
            'values' => Mage::getModel('sfc_cybersource/source_cctype')->toOptionArray(),
            'name' => 'cc_type',
        ));

        // credit card
        $fieldset->addField('customer_cardnumber', 'text', array(
            'label' => Mage::helper('sfc_cybersource')->__('Credit Card'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'customer_cardnumber',
        ));

        // cc_exp_month
        $fieldset->addField('cc_exp_month', 'select', array(
            'label' => Mage::helper('sfc_cybersource')->__('Expiration Month'),
            'class' => '',
            'required' => false,
            'values' => Mage::getModel('sfc_cybersource/source_month')->toOptionArray(),
            'name' => 'cc_exp_month',
        ));

        // cc_exp_month
        $fieldset->addField('cc_exp_year', 'select', array(
            'label' => Mage::helper('sfc_cybersource')->__('Expiration Year'),
            'class' => '',
            'required' => false,
            'values' => Mage::getModel('sfc_cybersource/source_year')->toOptionArray(),
            'name' => 'cc_exp_year',
        ));

        // Get payment profile from registry
        $model = Mage::registry('paymentprofile_data');
        // Tweak model with extra fields
        $model->setData('customer_id_readonly', $model->getData('customer_id'));
        $model->setData('email_readonly', $model->getData('email'));
        $model->setData('payment_token_readonly', $model->getData('payment_token'));
        // Set values for form
        $form->setValues($model->getData());
        // Set form on this widget
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
