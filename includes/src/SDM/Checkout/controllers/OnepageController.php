<?php
/**
 * Separation Degrees One
 *
 * Magento catalog customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Checkout
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

require_once Mage::getModuleDir('controllers', 'Mage_Checkout') . DS . 'OnepageController.php';

/**
 * Fixes rewrites from SDM_SavedQuote, OnePica_AvaTax, and IWD_AddressVerification.
 * Additionally has Logics for Onepage Checkout from SDM extensions.
 */
class SDM_Checkout_OnepageController extends Mage_Checkout_OnepageController
{
    /**
     * @see IWD_AddressVerification_OnepageController
     */
    protected $_cur_layout = null;

    /**
     * Validate ajax request and redirect on failure. Rewritten to check for
     * current quote item qtys against the actual inventory.
     *
     * @return bool
     */
    protected function _expireAjax()
    {
        /**
         * Check if quote items' qtys can be purchased
         */
        $cartValidated = Mage::helper('sdm_checkout/quote')->validateForCheckout();
        if (!$cartValidated) {
            $this->_ajaxRedirectResponse();
            return true;
        }

        return parent::_expireAjax();
    }

    /**
     * Checkout page
     *
     * 1. Removes minimum amount validation
     * 2. Resets the current quote with saved data
     * 3. Checkes for min. order amount, if applicable
     *
     * @return void
     */
    public function indexAction()
    {
        $minAmount = null;
        if (!$this->_meetsMinOrderAmount($minAmount)) {
            Mage::getSingleton('checkout/session')
                ->addError($this->__(
                    'Unable to checkout because minimum order amount is $%s.', $minAmount
                ));
            return $this->_redirect('checkout/cart');
        }

        // Validate that cart items all belong on checkout
        $cartValidated = Mage::helper('sdm_checkout/quote')->validateForCheckout();
        if (!$cartValidated) {
            return $this->_redirect('checkout/cart');
        }

        // If saved quote, then do special logic
        if (Mage::helper('savedquote')->isSavedQuoteSession()) {
            return $this->_indexLogicForSavedQuote();
        }

        return parent::indexAction();
    }

    /**
     * Special logic to run in the index method if saved quote
     *
     * @return null
     */
    protected function _indexLogicForSavedQuote()
    {
        /**
         * @see OnePica_AvaTax_OnepageController::indexAction
         */
        $session = Mage::getSingleton('checkout/session');
        $session->setPostType('onepage');

        /**
         * @see IWD_AddressVerification_OnepageController::indexAction
         */
        $this->getVerification()->getCheckout()->setShowValidationResults(false);

        // set results mode (need for javascript logic)
        $this->getVerification()->getCheckout()->setValidationResultsMode(false);

        // clear verification results from prevous checkout
        $this->getVerification()->getCheckout()->setShippingWasValidated(false);
        $this->getVerification()->getCheckout()->setBillingWasValidated(false);
        $this->getVerification()->getCheckout()->setBillingValidationResults(false);
        $this->getVerification()->getCheckout()->setShippingValidationResults(false);


        /**
         * SDM_SavedQuote
         * @see Mage_Checkout_OnepageController::indexAction
         */
        if (!Mage::helper('checkout')->canOnepageCheckout()) {
            Mage::getSingleton('checkout/session')->addError($this->__('The onepage checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
        }
        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');
            return;
        }
        // if (!$quote->validateMinimumAmount()) {
        //     $error = Mage::getStoreConfig('sales/minimum_order/error_message') ?
        //         Mage::getStoreConfig('sales/minimum_order/error_message') :
        //         Mage::helper('checkout')->__('Subtotal must exceed minimum order amount');

        //     Mage::getSingleton('checkout/session')->addError($error);
        //     $this->_redirect('checkout/cart');
        //     return;
        // }
        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure' => true)));
        $this->getOnepage()->initCheckout();

        Mage::helper('savedquote')->resetQuoteDataFromSavedQuote();

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Checkout'));
        $this->renderLayout();
    }

    /**
     * @see IWD_AddressVerification_OnepageController::saveBillingAction
     *
     * @return void
     */
    public function saveBillingAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        // set results mode (need for javascript logic)
        $this->getVerification()->getCheckout()->setValidationResultsMode(false);

        if ($this->getRequest()->isPost()) {
            if (!Mage::helper('addressverification')->isAddressVerificationEnabled()) {
                parent::saveBillingAction();
            } else {
                $validation_enabled = Mage::helper('addressverification')->getEnabledVerification();
                if (!$validation_enabled) {
                    parent::saveBillingAction();
                } else {
                    $this->getVerification()->setVerificationLib($validation_enabled);

                    $this->getVerification()->getCheckout()->setShippingWasValidated(false);
                    $this->getVerification()->getCheckout()->setShippingValidationResults(false);

                    $data = $this->getRequest()->getPost('billing', array());
                    $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
                    // if not valid addresses allowed for checkout
                    $allow_not_valid = Mage::helper('addressverification')->allowNotValidAddress();

                    if ($this->_checkChangedAddress($data, 'Billing', $customerAddressId, $validation_enabled)) {
                        $this->getVerification()->getCheckout()->setBillingWasValidated(false);
                    }

                    if ($allow_not_valid) {
                        $bill_was_validated = $this->getVerification()->getCheckout()->getBillingWasValidated();
                    } else {
                        $bill_was_validated = false;
                    }

                    if ($bill_was_validated) {
                        parent::saveBillingAction();
                    } else {
                        if (isset($data['email'])) {
                            $data['email'] = trim($data['email']);
                        }

                        $result = $this->getOnepage()->saveBilling($data, $customerAddressId);

                        if (!isset($result['error'])) {
                            // run validation
                            $bill_validate  = $this->getVerification()->validate_address('Billing');
                            if ($bill_validate) {
                                $this->getVerification()->getCheckout()->setBillingWasValidated(true);
                            } else {
                                $this->getVerification()->getCheckout()->setBillingWasValidated(false);
                            }

                            // check if exist validation errors
                            if (isset($bill_validate) && is_array($bill_validate)
                                && isset($bill_validate['error']) && !empty($bill_validate['error'])
                            ) {
                                $this->getVerification()->getCheckout()->setShowValidationResults('billing');

                                $result['update_section'] = array(
                                    'name' => 'address-validation',
                                    'html' => $this->_getAddressCandidatesHtml()
                                );

                                $this->getVerification()->getCheckout()->setShowValidationResults(false);

                                // clear validation results
                                $this->getVerification()->getCheckout()->setBillingValidationResults(false);

                                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                                return;
                            }

                            // clear validation results
                            $this->getVerification()->getCheckout()->setBillingValidationResults(false);

                            parent::saveBillingAction();
                        }
                    }
                }
            }
        }
    }

    /**
     * Shipping address save action
     *
     * 1.  Adding saved quote functionality to skip saving any of this data and
     *     just going to the next step
     *
     * @return void
     */
    public function saveShippingAction()
    {
        // If not saved quote, then continue to the other logics
        if (!Mage::helper('savedquote')->isSavedQuoteSession() || Mage::helper('sdm_preorder')->isQuotePreOrder()) {
            return $this->saveShippingIwdAction();
        }
        // Going through savedquote checkout without validating address
        // as shipping address has been already saved and cannot be changed.
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            // We don't want to save any of this data; just go to the next step
            // $data = $this->getRequest()->getPost('shipping', array());
            // $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
            // $result = $this->getOnepage()->saveShipping($data, $customerAddressId);

            // Complete this step
            Mage::getSingleton('checkout/session')
                ->setStepData('shipping', 'complete', true)
                ->setStepData('shipping_method', 'allow', true);

            // Reset saved quote data (in case anything changed)
            Mage::helper('savedquote')->resetQuoteDataFromSavedQuote();

            // Make empty result array
            $result = array();
            if (!isset($result['error'])) {
                $result['goto_section'] = 'shipping_method';
                $result['update_section'] = array(
                    'name' => 'shipping-method',
                    'html' => $this->_getShippingMethodsHtml()
                );
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Shipping method save action
     *
     * 1.  Adding saved quote functionality to skip saving any of this data and
     *     just going to the next step
     *
     * @return void
     */
    public function saveShippingMethodAction()
    {
        // If not saved quote, then fall back to parent behavior
        if (!Mage::helper('savedquote')->isSavedQuoteSession() || Mage::helper('sdm_preorder')->isQuotePreOrder()) {
            return parent::saveShippingMethodAction();
        }

        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            // We don't want to save any of this data; just go to the next step
            // $data = $this->getRequest()->getPost('shipping_method', '');
            // $result = $this->getOnepage()->saveShippingMethod($data);
            // $result will contain error data if shipping method is empty

            // Complete this step
            Mage::getSingleton('checkout/session')
                ->setStepData('shipping_method', 'complete', true)
                ->setStepData('payment', 'allow', true);

            // Reset saved quote data (in case anything changed)
            Mage::helper('savedquote')->resetQuoteDataFromSavedQuote();

            // Make empty result array
            $result = array();
            if (!$result) {
                Mage::dispatchEvent(
                    'checkout_controller_onepage_save_shipping_method',
                     array(
                          'request' => $this->getRequest(),
                          'quote'   => $this->getOnepage()->getQuote())
                    );
                $this->getOnepage()->getQuote()->collectTotals();
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

                $result['goto_section'] = 'payment';
                $result['update_section'] = array(
                    'name' => 'payment-method',
                    'html' => $this->_getPaymentMethodsHtml()
                );
            }
            $this->getOnepage()->getQuote()->collectTotals()->save();
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Save payment ajax action
     *
     * Sets either redirect or a JSON response
     *
     * @return void
     */
    public function savePaymentAction()
    {
        // If not saved quote, then fall back to parent behavior
        if (!Mage::helper('savedquote')->isSavedQuoteSession()) {
            return parent::savePaymentAction();
        }
        if ($this->_expireAjax()) {
            return;
        }
        try {
            if (!$this->getRequest()->isPost()) {
                $this->_ajaxRedirectResponse();
                return;
            }

            $data = $this->getRequest()->getPost('payment', array());
            $result = $this->getOnepage()->savePayment($data);

            Mage::helper('savedquote')->resetQuoteDataFromSavedQuote();

            // get section and redirect data
            $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if (empty($result['error']) && !$redirectUrl) {
                $this->loadLayout('checkout_onepage_review');
                $result['goto_section'] = 'review';
                $result['update_section'] = array(
                    'name' => 'review',
                    'html' => $this->_getReviewHtml()
                );
            }
            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = $this->__('Unable to set Payment Method.');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Updated layout
     *
     * @see IWD_AddressVerification_OnepageController
     *
     * @return mixed
     */
    protected function _getUpdatedLayout()
    {
        $this->_initLayoutMessages('checkout/session');
        if ($this->_cur_layout === null) {
            $layout = $this->getLayout();
            $update = $layout->getUpdate();
            $update->load('checkout_onepage_index');

            $layout->generateXml();
            $layout->generateBlocks();
            $this->_cur_layout = $layout;
        }

        return $this->_cur_layout;
    }

    /**
     * Address candidates html
     *
     * @see IWD_AddressVerification_OnepageController::_getAddressCandidatesHtml
     *
     * @return string
     */
    protected function _getAddressCandidatesHtml()
    {
        $layout = $this->_getUpdatedLayout();
        return $layout->getBlock('checkout.addresscandidates')->toHtml();
    }

    /**
     * Verification
     *
     * @see IWD_AddressVerification_OnepageController::getVerification
     *
     * @return Mage_Core_Model_Abstract
     */
    public function getVerification()
    {
        return Mage::getSingleton('addressverification/verification');
    }

    /**
     * Save shipping iwd action
     *
     * @see IWD_AddressVerification_OnepageController::saveShippingAction
     *
     * @return void
     */
    public function saveShippingIwdAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        // set results mode (need for javascript logic)
        $this->getVerification()->getCheckout()->setValidationResultsMode(false);

        if ($this->getRequest()->isPost()) {
            if (!Mage::helper('addressverification')->isAddressVerificationEnabled()) {
                parent::saveShippingAction();
            } else {
                $validation_enabled = Mage::helper('addressverification')->getEnabledVerification();
                if (!$validation_enabled) {
                    parent::saveShippingAction();
                } else {
                    $this->getVerification()->setVerificationLib($validation_enabled);

                    $data = $this->getRequest()->getPost('shipping', array());
                    $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
                    // if not valid addresses allowed for checkout
                    $allow_not_valid = Mage::helper('addressverification')->allowNotValidAddress();

                    if ($this->_checkChangedAddress($data, 'Shipping', $customerAddressId, $validation_enabled)) {
                        $this->getVerification()->getCheckout()->setShippingWasValidated(false);
                    }

                    if ($allow_not_valid) {
                        $ship_was_validated = $this->getVerification()->getCheckout()->getShippingWasValidated();
                    } else {
                        $ship_was_validated = false;
                    }

                    if ($ship_was_validated) {
                                        parent::saveShippingAction();
                    } else {
                        $result = $this->getOnepage()->saveShipping($data, $customerAddressId);

                        if (!isset($result['error'])) {                         // run validation
                            $ship_validate  = $this->getVerification()->validate_address('Shipping');
                            if ($ship_validate) {
                                $this->getVerification()->getCheckout()->setShippingWasValidated(true);
                            } else {
                                            $this->getVerification()->getCheckout()->setShippingWasValidated(false);
                            }

                            // check if exist validation errors
                            if (isset($ship_validate) && is_array($ship_validate)
                                && isset($ship_validate['error']) && !empty($ship_validate['error'])
                            ) {
                                $this->getVerification()->getCheckout()->setShowValidationResults('shipping');

                                $result['update_section'] = array(
                                    'name' => 'address-validation',
                                    'html' => $this->_getAddressCandidatesHtml()
                                );

                                $this->getVerification()->getCheckout()->setShowValidationResults(false);

                                // clear validation results
                                $this->getVerification()->getCheckout()->setShippingValidationResults(false);

                                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                                return;
                            }

                            // clear validation results
                            $this->getVerification()->getCheckout()->setShippingValidationResults(false);

                            parent::saveShippingAction();

                        }
                    }
                }
            }
        }
    }

    /**
     * Check changed address
     *
     * @param array  $data
     * @param string $addr_type
     * @param mixed  $addr_id
     * @param mixed  $check_city_street
     *
     * @see IWD_AddressVerification_OnepageController::_checkChangedAddress
     *
     * @return boolean
     */
    protected function _checkChangedAddress($data, $addr_type = 'Billing', $addr_id = false, $check_city_street = false)
    {
        $method = "get{$addr_type}Address";
        $address = $this->getVerification()->getQuote()->{$method}();

        if (!$addr_id) {
            if (($address->getRegionId() != $data['region_id']) || ($address->getPostcode() != $data['postcode'])
                || ($address->getCountryId() != $data['country_id'])
            ) {
                return true;
            }

            // if need to compare street and city
            if ($check_city_street) {
                // check street address
                $street1    = $address->getStreet();
                $street2    = $data['street'];

                if (is_array($street1)) {
                    if (is_array($street2)) {
                        if (trim(strtolower($street1[0])) != trim(strtolower($street2[0]))) {
                            return true;
                        }
                        if (isset($street1[1])) {
                            if (isset($street2[1])) {
                                if (trim(strtolower($street1[1])) != trim(strtolower($street2[1]))) {
                                    return true;
                                }
                            } else {
                                if (!empty($street1[1])) {
                                    return true;
                                }
                            }
                        } else {
                            if (isset($street2[1])) {
                                $s21    = trim($street2[1]);
                                if (!empty($s21)) {
                                    return true;
                                }
                            }
                        }
                    } else {
                        if (trim(strtolower($street1[0])) != trim(strtolower($street2))) {
                            return true;
                        }
                    }
                } else {
                    if (is_array($street2)) {
                        if (trim(strtolower($street1)) != trim(strtolower($street2[0]))) {
                            return true;
                        }
                    } else {
                        if (trim(strtolower($street1)) != trim(strtolower($street2))) {
                            return true;
                        }
                    }
                }

                // check city
                $add_city   = $address->getCity();
                $add_city   = trim(strtolower($add_city));
                if ($add_city   != trim(strtolower($data['city']))) {
                    return true;
                }
            }

            return false;
        } else {
            if ($addr_id != $address->getCustomerAddressId()) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Returns true if minimum order amount requirement is satisfied when applicable
     *
     * @param float $minAmount
     *
     * @return bool
     */
    protected function _meetsMinOrderAmount(&$minAmount)
    {
        return $this->getOnepage()->getQuote()->meetsMinimumOrderQuantity($minAmount);
    }

    /**
     * Create order action
     *
     * @return void
     */
    public function saveOrderAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*');
            return;
        }

        if ($this->_expireAjax()) {
            return;
        }

        $result = array();
        try {
            $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
            if ($requiredAgreements) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                $diff = array_diff($requiredAgreements, $postedAgreements);
                if ($diff) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = $this->__('Please agree to all the terms and conditions before placing the order.');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    return;
                }
            }

            $data = $this->getRequest()->getPost('payment', array());
            if ($data) {
                $data['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT
                    | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
                    | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
                    | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
                    | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
            }

            $this->getOnepage()->saveOrder();

            $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result['success'] = true;
            $result['error']   = false;
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                $result['error_messages'] = $message;
            }
            $result['goto_section'] = 'payment';
            $result['update_section'] = array(
                'name' => 'payment-method',
                'html' => $this->_getPaymentMethodsHtml()
            );
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();

            if (trim(strtolower($result['error_messages'])) == "failure contacting payment gateway!") {
                $result['error_messages'] = "Error authorizing payment. Please double check your payment information.";
            }

            $gotoSection = $this->getOnepage()->getCheckout()->getGotoSection();
            if ($gotoSection) {
                $result['goto_section'] = $gotoSection;
                $this->getOnepage()->getCheckout()->setGotoSection(null);
            }
            $updateSection = $this->getOnepage()->getCheckout()->getUpdateSection();
            if ($updateSection) {
                if (isset($this->_sectionUpdateFunctions[$updateSection])) {
                    $updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
                    $result['update_section'] = array(
                        'name' => $updateSection,
                        'html' => $this->$updateSectionFunction()
                    );
                }
                $this->getOnepage()->getCheckout()->setUpdateSection(null);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success']  = false;
            $result['error']    = true;
            $result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
        }
        $this->getOnepage()->getQuote()->save();
        /**
         * when there is redirect to third party, we don't want to save order yet.
         * we will save the order in return action.
         */
        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Order success action. Keep action for debugging.
     *
     * http://ellison.retail.local/checkout/onepage/success/
     */
    // public function successAction()
    // {
    //     $lastOrderId = 87;
    //     // $lastOrderId = 65;
    //     Mage::getSingleton('checkout/session')->setLastOrderId($lastOrderId);
    //     // Mage::getSingleton('checkout/session')->addNotice('test message');

    //     $this->loadLayout();
    //     $this->_initLayoutMessages('checkout/session');
    //     $this->renderLayout();

    //     // parent::successAction();
    // }
}
