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

$base = Mage::getModuleDir('controllers', 'Magestore_Sociallogin');
require_once $base . DS . "PopupController.php";

/**
 * SDM_Customer_PopupController class
 */
class SDM_Customer_PopupController extends Magestore_Sociallogin_PopupController
{
    /**
     * Login customer
     *
     * @return null
     */
    public function loginAction()
    {
        //$sessionId = session_id();
        $username = $this->getRequest()->getPost('socialogin_email', false);
        $password = $this->getRequest()->getPost('socialogin_password', false);
        $session  = Mage::getSingleton('customer/session');
        /*if (Mage::helper('persistent')->isEnabled() && Mage::helper('persistent')->isRememberMeEnabled()) {
            $rememberMeCheckbox = $this->getRequest()->getPost('persistent_remember_me');
            Mage::helper('persistent/session')->setRememberMeChecked((bool) $rememberMeCheckbox);
        }*/

        $result = array('success' => false);

        if ($username && $password) {
            try {
                $session->login($username, $password);

            } catch (Exception $e) {
                $importedCustomer = $this->_checkIfImportedCustomer($username);
                $result['error']  = $importedCustomer === false ? $e->getMessage() : $importedCustomer;
            }
            if (!isset($result['error'])) {
                $result['success'] = true;
            }
        } else {
            $result['error'] = $this->__('Please enter a username and password.');
        }
        //session_id($sessionId);
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    /**
     * Creates an account
     *
     * @return void
     */
    public function createAccAction()
    {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $result = array('success' => false, 'Already Logged In!');
        } else {
            $firstName   = $this->getRequest()->getPost('firstname', false);
            $lastName    = $this->getRequest()->getPost('lastname', false);
            $company     = $this->getRequest()->getPost('company', false);
            $inst        = $this->getRequest()->getPost('institution', false);
            $instd       = $this->getRequest()->getPost('institutiondescription', false);
            $pass        = $this->getRequest()->getPost('pass', false);
            $passConfirm = $this->getRequest()->getPost('passConfirm', false);
            $email       = $this->getRequest()->getPost('email', false);
            $customer    = Mage::getModel('customer/customer')
                ->setFirstname($firstName)
                ->setLastname($lastName)
                ->setCompany($company)
                ->setInstitution($inst)
                ->setInstitutionDescription($instd)
                ->setEmail($email)
                ->setPassword($pass)
                ->setConfirmation($passConfirm);
            try {
                $customer->save();
                Mage::dispatchEvent('customer_register_success',
                    array('customer' => $customer)
                );
                $result = array('success' => true);
                $session->setCustomerAsLoggedIn($customer);

                // Add email to lyris newsletter
                if ((string) $this->getRequest()->getPost('newsletter', false) === '1') {
                    Mage::getModel('sdm_lyris/api_account')
                        ->create(array('email' => $email));
                }
            } catch (Exception $e) {
                $result = array('success' => false, 'error' => $e->getMessage());
            }
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    /**
     * Returns the customer object if this user was imported and
     * reset their password for them. Else, returns false.
     *
     * @param  string $username
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
            $msg = $this->__(
                "Hello loyal customer! We have recently upgraded our website to improve " .
                "security and better serve you. As part of this process, we require that " .
                "your account password is reset before logging in.<br><br>An email has been " .
                "sent to <strong>%s</strong> with further instructions.<br><br>Thank you!",
                $customer->getEmail()
            );

            // Build error message html
            $wrapStart = "<div id='imported-account-bg'></div><div id='imported-account-msg'>";
            $closeLink = "<a href='#' class='close'>Continue &raquo;</a>";
            $wrapEnd   = "</div>";

            // Return JS as error message
            return "<script>
                jQuery('body').prepend(\"{$wrapStart}{$msg}{$closeLink}{$wrapEnd}\");
                jQuery('#imported-account-msg .close').click(function(){
                    jQuery('#imported-account-bg, #imported-account-msg').remove();
                    return false;
                });
            </script>";
        }

        return false;
    }
}
