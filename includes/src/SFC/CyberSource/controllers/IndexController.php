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

class SFC_CyberSource_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Authenticate customer
     */
    public function preDispatch()
    {
        // Call parent implementation
        parent::preDispatch();
        // Get customer session
        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');
        // Require logged in customer / authenticate customer
        if (!$customerSession->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    /**
     * Authorize customer from session against the specified profile.  This ensures customer is allowed to edit / update / delete profile.
     *
     * @param SFC_CyberSource_Model_payment_profile $paymentProfile Profile to authorize against current customer
     * @throws SFC_CyberSource_Helper_Gateway_Exception
     */
    protected function authorizeCustomerForProfile(SFC_CyberSource_Model_payment_profile $paymentProfile)
    {
        // Get customer session
        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');
        // Authorize customer for this profile - in other words profile must belong to this customer
        if ($customerSession->getCustomerId() != $paymentProfile->getData('customer_id')) {
            throw new SFC_CyberSource_Helper_Gateway_Exception('Customer not authorized to edit this profile!');
        }
    }

    /**
     * Customer Dashboard, payment profile grid
     */
    public function indexAction()
    {
        // Load layout
        $this->loadLayout();

        // Set page title
        /** @var Mage_Page_Block_Html_Head $headBlock */
        $headBlock = $this->getLayout()->getBlock('head');
        $headBlock->setTitle($this->__('My Credit Cards'));

        $this->renderLayout();
    }

    /**
     * New payment profile
     */
    public function newAction()
    {
        // Get core session
        /** @var Mage_Core_Model_Session $coreSession */
        $coreSession = Mage::getSingleton('core/session');
        // Get customer session
        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');

        // Load layout
        $this->loadLayout();

        // Set page title
        /** @var Mage_Page_Block_Html_Head $headBlock */
        $headBlock = $this->getLayout()->getBlock('head');
        $headBlock->setTitle($this->__('Enter New Saved Credit Card'));

        try {
            // Create new profile
            /** @var SFC_CyberSource_Model_payment_profile $paymentProfile */
            $paymentProfile = Mage::getModel('sfc_cybersource/payment_profile');
            // Init new cim profile with customer info
            $paymentProfile->initProfileWithCustomerDefault($customerSession->getCustomerId());
            // Pass fields to view for rendering
            $this->getLayout()->getBlock('payment_profile_edit')->setData('cim_profile', $paymentProfile);
        }
        catch (Exception $e) {
            $coreSession->addError('Failed to load new credit credit card page!');
            // Send customer back to grid        
            $this->_redirect('creditcards/*/');

            return;
        }

        // Render layout
        $this->renderLayout();
    }

    /**
     * Edit payment profile
     */
    public function editAction()
    {
        // Get core session
        /** @var Mage_Core_Model_Session $coreSession */
        $coreSession = Mage::getSingleton('core/session');

        // Load layout
        $this->loadLayout();

        // Set page title
        /** @var Mage_Page_Block_Html_Head $headBlock */
        $headBlock = $this->getLayout()->getBlock('head');
        $headBlock->setTitle($this->__('Edit Saved Credit Card'));

        try {
            // Get profile ID & load the profile
            $profileId = $this->getRequest()->getParam('id');
            /** @var SFC_CyberSource_Model_payment_profile $paymentProfile */
            $paymentProfile = Mage::getModel('sfc_cybersource/payment_profile')->load($profileId);
            // Authorize customer
            $this->authorizeCustomerForProfile($paymentProfile);
            // Get payment profile data from CyberSource
            $paymentProfile->retrieveProfileData();
            // Pass fields to view for rendering
            $this->getLayout()->getBlock('payment_profile_edit')->setData('cim_profile', $paymentProfile);
        }
        catch (Exception $e) {
            Mage::log('Error: ' . $e->getMessage(), Zend_Log::ERR, SFC_CyberSource_Helper_Data::LOG_FILE);
            $coreSession->addError('Failed to retrieve credit card for edit!');
            // Send customer back to grid        
            $this->_redirect('creditcards/*/');

            return;
        }

        $this->renderLayout();
    }

    /**
     * Save a new payment profile
     */
    public function saveAction()
    {
        // Get core session
        /** @var Mage_Core_Model_Session $coreSession */
        $coreSession = Mage::getSingleton('core/session');

        // Load layout
        $this->loadLayout();

        // Get post data
        $postData = $this->getRequest()->getPost();

        // Create profile model
        $paymentProfile = null;
        if ($profileId = $this->getRequest()->getParam('id')) {
            // Load existing profile
            /** @var SFC_CyberSource_Model_payment_profile $paymentProfile */
            $paymentProfile = Mage::getModel('sfc_cybersource/payment_profile')->load($profileId);
        }
        else {
            // Create new profile
            /** @var SFC_CyberSource_Model_payment_profile $paymentProfile */
            $paymentProfile = Mage::getModel('sfc_cybersource/payment_profile');
        }

        try {
            // Set attributes that can be saved in our DB and CyberSource
            $paymentProfile->addData($postData);
            // Authorize customer
            $this->authorizeCustomerForProfile($paymentProfile);
            // Now try to save payment profile to gateway
            $paymentProfile->saveProfileData();
            // Save our DB model
            $paymentProfile->save();

            $coreSession->addSuccess('Credit card was successfully saved');
        }
        catch (Exception $e) {
            Mage::log('Error: ' . $e->getMessage(), Zend_Log::ERR, SFC_CyberSource_Helper_Data::LOG_FILE);
            $coreSession->addError('Failed to save credit card!');
        }

        // Send customer back to grid
        $this->_redirect('creditcards/*/');
    }

    public function sop_saveAction()
    {
        // Get core session
        /** @var Mage_Core_Model_Session $coreSession */
        $coreSession = Mage::getSingleton('core/session');
        // Get customer session
        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');

        // Get post data
        $postData = $this->getRequest()->getPost();
        Mage::log('SOP POST back body:', Zend_Log::INFO, SFC_CyberSource_Helper_Data::LOG_FILE);
        Mage::log(print_r($postData, true), Zend_Log::INFO, SFC_CyberSource_Helper_Data::LOG_FILE);

        // Check if SOP POST was successful
        if (!isset($postData['decision']) || $postData['decision'] != 'ACCEPT') {
            Mage::log('Failed to save credit card!', Zend_Log::ERR, SFC_CyberSource_Helper_Data::LOG_FILE);
            if(isset($postData['decision'])) {
                Mage::log('Decision: ' . $postData['decision'], Zend_Log::ERR, SFC_CyberSource_Helper_Data::LOG_FILE);
            }
            if(isset($postData['message'])) {
                Mage::log('Message: ' . $postData['message'], Zend_Log::ERR, SFC_CyberSource_Helper_Data::LOG_FILE);
            }
            if(isset($postData['invalid_fields'])) {
                Mage::log('Invalid Fields: ' . $postData['invalid_fields'], Zend_Log::ERR, SFC_CyberSource_Helper_Data::LOG_FILE);
            }
            $coreSession->addError('Failed to save credit card!');
        }
        else {
            // Create profile model, for new or existing profile
            $paymentProfile = null;
            if (isset($postData['req_payment_token']) && strlen($paymentToken = $postData['req_payment_token'])) {
                // Load existing profile
                /** @var SFC_CyberSource_Model_payment_profile $paymentProfile */
                $paymentProfile = Mage::getModel('sfc_cybersource/payment_profile')->load($paymentToken, 'payment_token');
            }
            else {
                // Create new profile
                /** @var SFC_CyberSource_Model_payment_profile $paymentProfile */
                $paymentProfile = Mage::getModel('sfc_cybersource/payment_profile');
                // Init profile with customer info
                $paymentProfile->initProfileWithCustomerDefault($customerSession->getCustomerId());
            }

            try {
                // Set attributes that can be saved in our DB and CyberSource
                $paymentProfile->setProfileDataFromSOPPostBack($postData);
                // Save our DB model
                $paymentProfile->save();

                $coreSession->addSuccess('Credit card was successfully saved');
            }
            catch (Exception $e) {
                Mage::log('Error: ' . $e->getMessage(), Zend_Log::ERR, SFC_CyberSource_Helper_Data::LOG_FILE);
                $coreSession->addError('Failed to save credit card!');
            }
        }

        // Send customer back to grid
        $this->_redirect('creditcards/*/');
    }

    /**
     * Delete payment profile
     */
    public function deleteAction()
    {
        // Get core session
        /** @var Mage_Core_Model_Session $coreSession */
        $coreSession = Mage::getSingleton('core/session');

        // Get id of profile to delete
        $profileId = $this->getRequest()->getParam('id');
        try {
            // Load profile        
            /** @var SFC_CyberSource_Model_payment_profile $paymentProfile */
            $paymentProfile = Mage::getModel('sfc_cybersource/payment_profile')->load($profileId);
            // Authorize customer
            $this->authorizeCustomerForProfile($paymentProfile);
            // Delete profile
            $paymentProfile->deleteProfile();
            // Delete Magento DB row
            $paymentProfile->delete();

            $coreSession->addSuccess('Your credit card was successfully deleted.');
        }
        catch (Exception $e) {
            $coreSession->addError('Failed to delete saved credit card!');
        }

        // Send customer back to grid        
        $this->_redirect('creditcards/*/');
    }

}
