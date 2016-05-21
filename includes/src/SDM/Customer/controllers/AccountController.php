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

$base = Mage::getModuleDir('controllers', 'Mage_Customer');
require_once $base . DS . "AccountController.php";

/**
 * Customer account controller
 */
class SDM_Customer_AccountController extends Mage_Customer_AccountController
{
    /**
     * Change customer password action
     *
     * @return null
     */
    public function editPostAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/edit');
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            $customer = $this->_getSession()->getCustomer();

            $customerForm = $this->_getModel('customer/form');
            $customerForm->setFormCode('customer_account_edit')
                ->setEntity($customer);

            $customerData = $customerForm->extractData($request);

            $errors = array();
            $customerErrors = $customerForm->validateData($customerData);
            if ($customerErrors !== true) {
                $errors = array_merge($customerErrors, $errors);
            } else {
                $customerForm->compactData($customerData);
                $errors = array();

                // If password change was requested then add it to common validation scheme
                if ($request->getParam('change_password')) {
                    $currPass   = $request->getPost('current_password');
                    $newPass    = $request->getPost('password');
                    $confPass   = $request->getPost('confirmation');

                    $oldPass = $this->_getSession()->getCustomer()->getPasswordHash();
                    if ($this->_getHelper('core/string')->strpos($oldPass, ':')) {
                        list($_salt, $salt) = explode(':', $oldPass);
                    } else {
                        $salt = false;
                    }

                    if ($customer->hashPassword($currPass, $salt) == $oldPass) {
                        if (strlen($newPass)) {
                            /**
                             * Set entered password and its confirmation - they
                             * will be validated later to match each other and be of right length
                             */
                            $customer->setPassword($newPass);
                            $customer->setPasswordConfirmation($confPass);
                        } else {
                            $errors[] = $this->__('New password field cannot be empty.');
                        }
                    } else {
                        $errors[] = $this->__('Invalid current password');
                    }
                }

                // Validate account and compose list of errors if any
                $customerErrors = $customer->validate();
                if (is_array($customerErrors)) {
                    $errors = array_merge($errors, $customerErrors);
                }
            }

            if (!empty($errors)) {
                $this->_getSession()->setCustomerFormData($request->getPost());
                foreach ($errors as $message) {
                    $this->_getSession()->addError($message);
                }
                $this->_redirect('*/*/edit');
                return $this;
            }

            // EEUS Only Attributes
            if (Mage::helper('sdm_core')->isSite(SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_ED)) {
                $customer->setInstitution($request->getParam('institution'));
                $customer->setInstitutionDescription($request->getParam('institution_description'));
            }

            // ERUS Only Attributes
            if (Mage::helper('sdm_core')->isSite(SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_RE)) {
                $customer->setCompany($request->getParam('company'));
            }

            try {
                $customer->cleanPasswordsValidationData();
                $customer->save();
                $this->_getSession()->setCustomer($customer)
                    ->addSuccess($this->__('The account information has been saved.'));

                $this->_redirect('customer/account');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->setCustomerFormData($request->getPost())
                    ->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->setCustomerFormData($request->getPost())
                    ->addException($e, $this->__('Cannot save the customer.'));
            }
        }

        $this->_redirect('*/*/edit');
    }

    /**
     * Rewritten to use the custom customer validation
     *
     * @see Mage_Customer_AccountController::resetPasswordPostAction()
     *
     * @return void
     */
    public function resetPasswordPostAction()
    {
        list($customerId, $resetPasswordLinkToken) = $this->_getRestorePasswordParameters($this->_getSession());
        if (empty($resetPasswordLinkToken)) {
            $resetPasswordLinkToken = (string) $this->getRequest()->getQuery('token');
        }
        if (empty($customerId)) {
            $customerId = (int) $this->getRequest()->getQuery('id');
        }
        $password = (string) $this->getRequest()->getPost('password');
        $passwordConfirmation = (string) $this->getRequest()->getPost('confirmation');
        try {
            $this->_validateResetPasswordLinkToken($customerId, $resetPasswordLinkToken);
        } catch (Exception $exception) {
            $this->_getSession()->addError($this->_getHelper('customer')->__('Your password reset link has expired.'));
            $this->_redirect('*/*/');
            return;
        }

        $errorMessages = array();
        if (iconv_strlen($password) <= 0) {
            array_push($errorMessages, $this->_getHelper('customer')->__('New password field cannot be empty.'));
        }
        // @var $customer Mage_Customer_Model_Customer
        $customer = $this->_getModel('customer/customer')->load($customerId);

        $customer->setPassword($password);
        $customer->setPasswordConfirmation($passwordConfirmation);
        $validationErrorMessages = $customer->validateForPasswordReset();
        if (is_array($validationErrorMessages)) {
            $errorMessages = array_merge($errorMessages, $validationErrorMessages);
        }

        if (!empty($errorMessages)) {
            $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
            foreach ($errorMessages as $errorMessage) {
                $this->_getSession()->addError($errorMessage);
            }
            $this->_redirect('*/*/resetpassword', array(
                'id' => $customerId,
                'token' => $resetPasswordLinkToken
            ));
            return;
        }

        try {
            // Empty current reset password token i.e. invalidate it
            $customer->setRpToken(null);
            $customer->setRpTokenCreatedAt(null);
            $customer->cleanPasswordsValidationData();
            $customer->save();
            $this->_getSession()->addSuccess($this->_getHelper('customer')->__('Your password has been updated.'));
            $this->_redirect('*/*/login');
        } catch (Exception $exception) {
            $this->_getSession()->addException($exception, $this->__('Cannot save a new password.'));
            $this->_redirect('*/*/resetpassword', array(
                'id' => $customerId,
                'token' => $resetPasswordLinkToken
            ));
            return;
        }
    }

    /**
     * Login post action
     *
     * @return void
     */
    public function loginPostAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*/');
            return;
        }

        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session = $this->_getSession();

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $session->login($login['username'], $login['password']);
                    if ($session->getCustomer()->getIsJustConfirmed()) {
                        $this->_welcomeCustomer($session->getCustomer(), true);
                    }
                } catch (Mage_Core_Exception $e) {
                    $message = $this->_checkIfImportedCustomer($login['username']);
                    if ($message === false) {
                        switch ($e->getCode()) {
                            case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                                $value = $this->_getHelper('customer')->getEmailConfirmationUrl($login['username']);
                                $message = $this->_getHelper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                                break;
                            case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                                $message = $e->getMessage();
                                break;
                            default:
                                $message = $e->getMessage();
                        }
                    }

                    $session->addError($message);
                    $session->setUsername($login['username']);
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                $session->addError($this->__('Login and password are required.'));
            }
        }

        $this->_loginPostRedirect();
    }

    /**
     * Returns the error message and sends an email to the customer a new password.
     * Otherwise, returns false.
     *
     * @param string $username
     *
     * @return bool|string
     */
    protected function _checkIfImportedCustomer($username)
    {
        $customer = Mage::getModel('customer/customer');
        $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
        $customer->loadByEmail($username);

        if ($customer->getPasswordHash() === 'imported_customer') {
            $newPassword = $customer->generatePassword();
            $customer->changePassword($newPassword, false);
            $customer->sendPasswordReminderEmail();

            // Display message
            $message = $this->__(
                "Hello loyal customer! We have recently upgraded our website to improve ".
                "security and better serve you. As part of this process, we require that ".
                "your account password is reset before logging in.<br><br>An email has been ".
                "sent to <strong>%s</strong> with further instructions.<br><br>Thank you!",
                $customer->getEmail()
            );

            return $message;
        }

        return false;
    }
}
