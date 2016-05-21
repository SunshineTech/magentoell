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

class SFC_CyberSource_OnepageController extends Mage_Checkout_Controller_Action
{

    /**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true')
            ->sendResponse();
        return $this;
    }

    /**
     * Validate ajax request and redirect on failure
     *
     * @return bool
     */
    protected function _expireAjax()
    {
        if (!$this->getOnepage()->getQuote()->hasItems()
            || $this->getOnepage()->getQuote()->getHasError()
            || $this->getOnepage()->getQuote()->getIsMultiShipping()) {
            $this->_ajaxRedirectResponse();
            return true;
        }
        $action = $this->getRequest()->getActionName();
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)
            && !in_array($action, array('index', 'progress'))) {
            $this->_ajaxRedirectResponse();
            return true;
        }

        return false;
    }

    /**
     * Save checkout method
     * This method is called back by CyberSource after SOP POST
     */
    public function sop_saveAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        try {
            if ($this->getRequest()->isPost()) {
                $postData = $this->getRequest()->getPost();
                // Log response from CyberSource
                Mage::log('CyberSource POST back response: ' . print_r($postData, true), Zend_Log::INFO, SFC_CyberSource_Helper_Data::LOG_FILE);
                // Check success of SOP
                if (isset($postData['decision']) && isset($postData['reason_code']) && isset($postData['payment_token']) &&
                    $postData['decision'] == "ACCEPT" && $postData['reason_code'] == 100) {

                    // Set profile as payment method on quote
                    $payment = $this->getOnepage()->getQuote()->getPayment();
                    // Save token
                    $payment->setAdditionalInformation('payment_token', $postData['payment_token']);
                    $payment->setData('cybersource_token', $postData['payment_token']);
                    // Save payment to DB
                    $payment->save();

                    // Return success
                    $iframeBlock = $this->getLayout()->createBlock('sfc_cybersource/checkout_sopIframe');
                    $iframeBlock->setJson(json_encode(array(
                        'status' => 'success',
                        'payment_token' => $postData['payment_token'],
                    )));
                    $this->getResponse()->setBody($iframeBlock->toHtml());
                    return;
                }
            }
        }
        catch (Exception $e) {
            Mage::log('SOP Create token failed.  Error message: ' . $e->getMessage(), Zend_Log::INFO, SFC_CyberSource_Helper_Data::LOG_FILE);
        }

        // Return failed response
        $iframeBlock = $this->getLayout()->createBlock('sfc_cybersource/checkout_sopIframe');
        $iframeBlock->setJson(json_encode(array(
            'status' => 'failed',
        )));
        $this->getResponse()->setBody($iframeBlock->toHtml());
        return;
    }

    public function sop_iframeAction()
    {
        $iframeBlock = $this->getLayout()->createBlock('sfc_cybersource/checkout_sopIframe');
        $this->getResponse()->setBody($iframeBlock->toHtml());
    }

}
