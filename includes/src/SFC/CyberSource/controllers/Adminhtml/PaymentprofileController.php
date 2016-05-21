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

class SFC_CyberSource_Adminhtml_PaymentprofileController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Set admin breadcrumbs
     */
    protected function _initAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('customers/sfc_cybersource');
        $this->_addBreadcrumb(
            Mage::helper('adminhtml')->__('Payment Profile  Manager'),
            Mage::helper('adminhtml')->__('Payment Profile Manager'));

        return $this;
    }

    /**
     * Set default page title
     */
    public function indexAction()
    {
        $this->_title($this->__('Customer'));
        $this->_title($this->__('Payment Profiles'));

        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Edit existing payment profile
     */
    public function editAction()
    {
        $this->_title($this->__('AuthnetToken'));
        $this->_title($this->__('Customer'));
        $this->_title($this->__('Edit Item'));

        $profileId = $this->getRequest()->getParam('id');
        $model = Mage::getModel('sfc_cybersource/payment_profile')->load($profileId);
        if ($model->getId()) {

            // Retrieve extra data fields from gateway API
            try {
                $model->retrieveProfileData();
            }
            catch (SFC_CyberSource_Helper_Gateway_Exception $eCyberSource) {
                Mage::getSingleton('adminhtml/session')->addError('Failed to retrieve saved credit card info from CyberSource!');
                // Send customer back to saved credit cards grid
                $this->_redirect('adminhtml/customer/edit/tab/customer_info_tabs_paymentprofile',
                    array('id' => $model->getCustomerId()));

                return;
            }
            // Save profile in registry
            Mage::register('paymentprofile_data', $model);
            Mage::register('customer_id', $model->getCustomerId());

            $this->loadLayout();
            $this->_setActiveMenu('sfc_cybersource/paymentprofile');
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Payment Profile'), Mage::helper('adminhtml')->__('Payment Profile'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Payment Profile'), Mage::helper('adminhtml')->__('Information'));
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('sfc_cybersource/adminhtml_customer_paymentprofiles_edit'));
            $this->_addLeft($this->getLayout()->createBlock('sfc_cybersource/adminhtml_customer_paymentprofiles_edit_tabs'));
            $this->renderLayout();
        }
        else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sfc_cybersource')->__('Failed to find saved credit card.'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Create new CIM payment profile
     */
    public function newAction()
    {
        $this->_title($this->__('AuthnetToken'));
        $this->_title($this->__('Payment Profile'));
        $this->_title($this->__('New Payment Profile'));

        // Create new model for editing & init with customer fields
        $model = Mage::getModel('sfc_cybersource/payment_profile');
        $model->initProfileWithCustomerDefault($this->getRequest()->getParam('customerid'));

        // Save profile in registry
        Mage::register('paymentprofile_data', $model);
        Mage::register('customer_id', $model->getCustomerId());

        $this->loadLayout();
        $this->_setActiveMenu('sfc_cybersource/paymentprofile');

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Payment Profile Manager'), Mage::helper('adminhtml')
            ->__('Payment Profile Manager'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Info'), Mage::helper('adminhtml')->__('info'));


        $this->_addContent($this->getLayout()->createBlock('sfc_cybersource/adminhtml_customer_paymentprofiles_edit'));
        $this->_addLeft($this->getLayout()->createBlock('sfc_cybersource/adminhtml_customer_paymentprofiles_edit_tabs'));

        $this->renderLayout();

    }

    /**
     * Save data to existing payment profile
     */
    public function saveAction()
    {
        $postData = $this->getRequest()->getPost();

        if ($profileId = $this->getRequest()->getParam('id')) {
            $model = Mage::getModel('sfc_cybersource/payment_profile')->load($profileId);
        }
        else {
            $model = Mage::getModel('sfc_cybersource/payment_profile');
        }

        if ($postData) {

            try {
                try {
                    // Save post data to model
                    $model->addData($postData);
                    // Now try to save payment profile to gateway
                    $model->saveProfileData();
                }
                catch (SFC_CyberSource_Helper_Gateway_Exception $eCyberSource) {
                    Mage::getSingleton('adminhtml/session')->addError('Failed to save credit card to CyberSource!');
                    // Send customer back to saved credit cards grid
                    $this->_redirect('adminhtml/customer/edit/tab/customer_info_tabs_paymentprofile',
                        array('id' => $postData['customer_id']));

                    return;
                }

                // Now save model
                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Saved credit card ' . $model->getCustomerCardnumber() . '.'));
                Mage::getSingleton('adminhtml/session')->setCustomerData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));

                    return;
                }
                $this->_redirect('adminhtml/customer/edit/tab/customer_info_tabs_paymentprofile',
                    array('id' => $model->getCustomerId()));

                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError('Failed to save credit card!');
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // Send customer back to saved credit cards grid
                $this->_redirect('adminhtml/customer/edit/tab/customer_info_tabs_paymentprofile',
                    array('id' => $postData['customer_id']));
            }
        }

        // Send customer back to saved credit cards grid
        $this->_redirect('adminhtml/customer/edit/tab/customer_info_tabs_paymentprofile', array('id' => $postData['customer_id']));
    }

    /**
     * Delete payment profile
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('sfc_cybersource/payment_profile')->load($this->getRequest()->getParam('id'));
                $customerId = $model->getData('customer_id');
                // Delete payment profile
                try {
                    $model->deleteProfile();
                }
                catch (SFC_CyberSource_Helper_Gateway_Exception $eCyberSource) {
                    Mage::getSingleton('adminhtml/session')->addError('Failed to delete payment profile at CyberSource!');
                    // Send back to saved credit cards grid
                    $this->_redirect('adminhtml/customer/edit/tab/customer_info_tabs_paymentprofile', array('id' => $customerId));

                    return;
                }
                // Now delete Magento model
                $model->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Saved credit card ' . $model->getCustomerCardnumber() . ' deleted.'));
                // Send back to saved credit cards grid
                $this->_redirect('adminhtml/customer/edit/tab/customer_info_tabs_paymentprofile', array('id' => $customerId));
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError('Failed to delete saved credit card!');
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // Send admin back to manage customers
                $this->_redirect('adminhtml/customer/index');
            }
        }
    }

    /**
     * Delete multiple items
     */
    public function massRemoveAction()
    {
        try {
            $profileIds = $this->getRequest()->getPost('ids', array());
            $customerId = Mage::getModel('sfc_cybersource/payment_profile')->load($profileIds[0])->getCustomerId();
            foreach ($profileIds as $profileId) {
                $model = Mage::getModel('sfc_cybersource/payment_profile')->load($profileId);
                try {
                    try {
                        // Delete payment profile
                        $model->deleteProfile();
                    }
                    catch (SFC_CyberSource_Helper_Gateway_Exception $eCyberSource) {
                        $message =
                            'Failed to delete payment profile at CyberSource for card: ' . $model->getCustomerCardnumber();
                        Mage::getSingleton('adminhtml/session')->addError($message);
                    }
                    // Now delete Magento model
                    $model->delete();
                }
                catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Deleted saved credit card(s).'));
        }
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError('Failed to delete saved credit card!');
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('adminhtml/customer/edit/tab/customer_info_tabs_paymentprofile', array('id' => $customerId));
    }

    /**
     * Profile grid for AJAX request
     */
    public function gridAction()
    {
        $customer = Mage::getModel('customer/customer')->load($this->getRequest()->getParam('id'));
        Mage::register('current_customer', $customer);
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('sfc_cybersource/adminhtml_customer_paymentprofiles_paymentprofile')->toHtml()
        );
    }

}
