<?php
/**
 * Separation Degrees Media
 *
 * Cybersource Modification
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Cybersource
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Cybersource_Model_Soap class
 */
class SDM_Cybersource_Model_Soap extends Mage_Cybersource_Model_Soap
{
    /**
     * Cybersource SOAP response codes.
     */
    const RESPONSE_CODE_ERROR_AVS = '200';
    const RESPONSE_CODE_ERROR_CVV = '230';

    /**
     * Authorizing payment
     *
     * @param Varien_Object $payment
     * @param float         $amount
     *
     * @return Mage_Cybersource_Model_Soap
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $error = false;
        $soapClient = $this->getSoapApi();
        $this->iniRequest();

        $ccAuthService = new stdClass();
        $ccAuthService->run = "true";
        $this->_request->ccAuthService = $ccAuthService;
        $this->addBillingAddress($payment->getOrder()->getBillingAddress(), $payment->getOrder()->getCustomerEmail());
        $this->addShippingAddress($payment->getOrder()->getShippingAddress());
        $this->addCcInfo($payment);

        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $payment->getOrder()->getBaseCurrencyCode();
        $purchaseTotals->grandTotalAmount = $amount;
        $this->_request->purchaseTotals = $purchaseTotals;

        try {
            $result = $soapClient->runTransaction($this->_request);

            if ($result->reasonCode == self::RESPONSE_CODE_SUCCESS) {
                $payment->setLastTransId($result->requestID)
                    ->setCcTransId($result->requestID)
                    ->setTransactionId($result->requestID)
                    ->setIsTransactionClosed(0)
                    ->setCybersourceToken($result->requestToken)
                    ->setCcAvsStatus($result->ccAuthReply->avsCode);
                /*
                 * checking if we have cvCode in response bc
                 * if we don't send cvn we don't get cvCode in response
                 */
                if (isset($result->ccAuthReply->cvCode)) {
                    $payment->setCcCidStatus($result->ccAuthReply->cvCode);
                }

            } elseif ($result->reasonCode == self::RESPONSE_CODE_ERROR_AVS
                || $result->reasonCode == self::RESPONSE_CODE_ERROR_CVV
            ) {
                /**
                 * Perform authorization reversal
                 * http://apps.cybersource.com/library/documentation/dev_guides/
                 * CC_Svcs_SO_API/html/wwhelp/wwhimpl/js/html/wwhelp.htm#href=
                 * processing.05.2.html
                 */
                $requestForReversal = new stdClass();
                $requestForReversal->merchantID = $this->getConfigData('merchant_id');
                $requestForReversal->merchantReferenceCode = $this->_generateReferenceCode();
                $requestForReversal->merchantReferenceCode = $this->_request->merchantReferenceCode;
                $requestForReversal->ccAuthReversalService = new stdClass;
                $requestForReversal->ccAuthReversalService->run = 'true'; // MUST be a string
                $requestForReversal->ccAuthReversalService->authRequestID = $result->requestID;
                $requestForReversal->ccAuthReversalService->authRequestToken = $result->requestToken;
                $requestForReversal->purchaseTotals = new stdClass;
                $requestForReversal->purchaseTotals->currency = $this->_request->purchaseTotals->currency;
                $requestForReversal->purchaseTotals->grandTotalAmount = $this->_request->purchaseTotals->grandTotalAmount;

                // Make the request
                $reversalResult = $soapClient->runTransaction($requestForReversal);

                $error = $this->_getDetailedErrorMessage($result);

            } else {
                Mage::log($result->reasonCode, null, 'cybersource_soap_response_code.log');
                $error = $this->_getDetailedErrorMessage($result);
            }

        } catch (Exception $e) {
            Mage::throwException(
                Mage::helper('cybersource')->__('Gateway request error: %s', $e->getMessage())
            );
        }

        if ($error !== false) {
            Mage::throwException($error);
        }
        return $this;
    }

    /**
     * Capturing payment
     *
     * @param Varien_Object $payment
     * @param float         $amount
     *
     * @return Mage_Cybersource_Model_Soap
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $error = false;
        $soapClient = $this->getSoapApi();
        $this->iniRequest();

        if ($payment->getParentTransactionId() && $payment->getCybersourceToken()) {
            $ccCaptureService = new stdClass();
            $ccCaptureService->run = 'true';
            $ccCaptureService->authRequestToken = $payment->getCybersourceToken();
            $ccCaptureService->authRequestID = $payment->getParentTransactionId();
            $this->_request->ccCaptureService = $ccCaptureService;

            $item0 = new stdClass();
            $item0->unitPrice = $amount;
            $item0->id = 0;
            $this->_request->item = array($item0);
        } else {
            $ccAuthService = new stdClass();
            $ccAuthService->run = 'true';
            $this->_request->ccAuthService = $ccAuthService;

            $ccCaptureService = new stdClass();
            $ccCaptureService->run = 'true';
            $this->_request->ccCaptureService = $ccCaptureService;

            $this->addBillingAddress(
                $payment->getOrder()->getBillingAddress(),
                $payment->getOrder()->getCustomerEmail()
            );
            $this->addShippingAddress($payment->getOrder()->getShippingAddress());
            $this->addCcInfo($payment);

            $purchaseTotals = new stdClass();
            $purchaseTotals->currency = $payment->getOrder()->getBaseCurrencyCode();
            $purchaseTotals->grandTotalAmount = $amount;
            $this->_request->purchaseTotals = $purchaseTotals;
        }
        try {
            $result = $soapClient->runTransaction($this->_request);
            if ($result->reasonCode==self::RESPONSE_CODE_SUCCESS) {
                /*
                for multiple capture we need to use the latest capture transaction id
                */
                $payment->setLastTransId($result->requestID)
                    ->setLastCybersourceToken($result->requestToken)
                    ->setCcTransId($result->requestID)
                    ->setTransactionId($result->requestID)
                    ->setIsTransactionClosed(0)
                    ->setCybersourceToken($result->requestToken);
            } else {
                $error = $this->_getDetailedErrorMessage($result);
            }
        } catch (Exception $e) {
            Mage::throwException(
                Mage::helper('cybersource')->__('Gateway request error: %s', $e->getMessage())
            );
        }
        if ($error !== false) {
            Mage::throwException($error);
        }
        return $this;
    }

    /**
     * Void the payment transaction
     *
     * @param Mage_Sale_Model_Order_Payment $payment
     *
     * @return Mage_Cybersource_Model_Soap
     */
    public function void(Varien_Object $payment)
    {
        $error = false;
        if ($payment->getParentTransactionId() && $payment->getCybersourceToken()) {
            $soapClient = $this->getSoapApi();
            $this->iniRequest();
            $ccAuthReversalService = new stdClass();
            $ccAuthReversalService->run = "true";
            $ccAuthReversalService->authRequestID = $payment->getParentTransactionId();
            $ccAuthReversalService->authRequestToken = $payment->getCybersourceToken();
            $this->_request->ccAuthReversalService = $ccAuthReversalService;

            $purchaseTotals = new stdClass();
            $purchaseTotals->currency = $payment->getOrder()->getBaseCurrencyCode();
            $purchaseTotals->grandTotalAmount = $payment->getBaseAmountAuthorized();
            $this->_request->purchaseTotals = $purchaseTotals;

            try {
                $result = $soapClient->runTransaction($this->_request);
                if ($result->reasonCode==self::RESPONSE_CODE_SUCCESS) {
                    $payment->setTransactionId($result->requestID)
                        ->setCybersourceToken($result->requestToken)
                        ->setIsTransactionClosed(1);
                } else {
                    $error = $this->_getDetailedErrorMessage($result);
                }
            } catch (Exception $e) {
                Mage::throwException(
                    Mage::helper('cybersource')->__('Gateway request error: %s', $e->getMessage())
                );
            }
        } else {
            $error = Mage::helper('cybersource')->__('Invalid transaction id or token');
        }
        if ($error !== false) {
            Mage::throwException($error);
        }
        return $this;
    }

    /**
     * Refund the payment transaction
     *
     * @param Mage_Sale_Model_Order_Payment $payment
     * @param flaot                         $amount
     *
     * @return Mage_Cybersource_Model_Soap
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $error = false;
        if ($payment->getParentTransactionId() && $payment->getRefundCybersourceToken() && $amount>0) {
            $soapClient = $this->getSoapApi();
            $this->iniRequest();
            $ccCreditService = new stdClass();
            $ccCreditService->run = "true";
            $ccCreditService->captureRequestToken = $payment->getCybersourceToken();
            $ccCreditService->captureRequestID = $payment->getParentTransactionId();
            $this->_request->ccCreditService = $ccCreditService;

            $purchaseTotals = new stdClass();
            $purchaseTotals->grandTotalAmount = $amount;
            $this->_request->purchaseTotals = $purchaseTotals;

            try {
                $result = $soapClient->runTransaction($this->_request);
                if ($result->reasonCode==self::RESPONSE_CODE_SUCCESS) {
                    $payment->setTransactionId($result->requestID)
                        ->setIsTransactionClosed(1)
                        ->setLastCybersourceToken($result->requestToken);
                } else {
                    $error = $this->_getDetailedErrorMessage($result);
                }
            } catch (Exception $e) {
                Mage::throwException(
                    Mage::helper('cybersource')->__('Gateway request error: %s', $e->getMessage())
                );
            }
        } else {
            $error = Mage::helper('cybersource')->__('Error in refunding the payment.');
        }
        if ($error !== false) {
            Mage::throwException($error);
        }
        return $this;
    }

    /**
     * Show a more detailed error message
     *
     * @param mixed $result
     *
     * @return string
     */
    protected function _getDetailedErrorMessage($result)
    {
        $codes = $this->getResponseCodes();
        $error = Mage::helper('cybersource')->__(
            'Your card could not be authorized! Please correct any details below and try again, try another card or contact us for further assistance.'
        );
        if ($result && isset($codes[$result->reasonCode])) {
            $error .= "\n\n" . Mage::helper('cybersource')->__($codes[$result->reasonCode]['description']);
        }
        return $error;
    }

    /**
     * Get list of response code
     *
     * @return array
     */
    public function getResponseCodes()
    {
        if (!$this->hasResponseCodes()) {
            $this->setResponseCodes(Mage::helper('core')->jsonDecode(file_get_contents(
                Mage::getModuleDir('etc', 'SDM_Cybersource') . DS . 'responseCode.json'
            )));
        }
        return parent::getResponseCodes();
    }
}
