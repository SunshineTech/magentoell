<?php
/**
 * Separation Degrees One
 *
 * eCal Lite download request extension
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_EcalLite
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_EcalLite_IndexController class
 */
class SDM_EcalLite_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Form page
     *
     * @return void
     */
    public function indexAction()
    {
        // Mage::registry('form_inputs');

        $this->loadLayout();
        $this->getLayout()->getBlock('head')
            ->setTitle($this->__('eCAL Lite Software Activation'));

        // Set form variable, if available
        $inputs = Mage::getSingleton('core/session')->getPostData();
        if ($inputs) {
            Mage::register('form_inputs', $inputs);
            Mage::getSingleton('core/session')->unsPostData();
        }

        $this->renderLayout();
    }

    /**
     * Form submit action
     *
     * @return void
     */
    public function submitAction()
    {
        $post = $this->getRequest()->getPost();

        if (empty($post)) {
            return $this->_redirect('*/');

        } else {
            $errors = $this->_validateFormInputs($post);

            if ($errors !== true) {
                Mage::getSingleton('core/session')->addError(implode(' ', $errors));

                return $this->_redirect('*/');

                // Input ready to be send over to eCal Lite
            } else {
                $response = Mage::helper('ecallite')->request($post);
                $result = Mage::helper('ecallite')->processResponse($response);
                $code = implode('-', $post['codes']);
                $email = $post['email'];

                if ($result['success'] === true) {
                    // Record successful authorizations
                    try {
                        $authorizedRequest = Mage::getModel('ecallite/request')
                            ->setFirstname($post['firstname'])
                            ->setLastname($post['lastname'])
                            ->setEmail($email)
                            ->setCode($code)
                            ->setStatus(SDM_EcalLite_Helper_Data::STATUS_AUTHORIZED_CODE)
                            ->setRequestedAt(Mage::getSingleton('core/date')->gmtDate())
                            ->setWebsiteId(Mage::app()->getWebsite()->getId())
                            ->save();
                    } catch (Exception $e) {
                        // Don't let customer know. Only log it.
                        Mage::helper('ecallite')->log(
                            'Sucessful authorization but failed to record. Email: '
                                . "$email. Code: $code"
                        );
                    }

                    Mage::getSingleton('core/session')->addSuccess($result['message']);
                    Mage::getSingleton('core/session')->unsPostData();

                } else {
                    // Set session variable for the next page
                    Mage::getSingleton('core/session')->setPostData($post);
                    Mage::getSingleton('core/session')->addError($result['message']);
                }

                return $this->_redirect('*/');
            }
        }
    }

    /**
     * Validates the form inputs
     *
     * @param array $post
     *
     * @return true|array
     */
    protected function _validateFormInputs($post)
    {
        $errors = array();

        if (!Zend_Validate::is($post['firstname'], 'NotEmpty')) {
            $errors[] = Mage::helper('ecallite')->__('First Name is required field.');
        }
        if (!Zend_Validate::is($post['lastname'], 'NotEmpty')) {
            $errors[] = Mage::helper('ecallite')->__('Last Name is required field.');
        }
        if (!Zend_Validate::is($post['email'], 'EmailAddress')) {
            $errors[] = Mage::helper('ecallite')->__('Please enter a valid email.');
        }
        if ($post['email'] != $post['confirm_email']) {
            $errors[] = Mage::helper('ecallite')->__('eCal Code must have 5 parts.');
        }
        if (count($post['codes']) != 5) {
            $errors[] = Mage::helper('ecallite')->__('eCal Code must have 5 parts.');
        }

        foreach ($post['codes'] as $code) {
            if (!Zend_Validate::is($code, 'Alnum')) {
                $errors[] = Mage::helper('ecallite')->__('eCal Code must have 5 parts.');
                break;
            }
        }

        if (empty($errors)) {
            return true;
        }

        return $errors;
    }
}
