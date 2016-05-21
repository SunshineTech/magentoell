<?php
/**
 * Separation Degrees One
 *
 * Valutec Giftcard Integration
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Valutec
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

require_once Mage::getModuleDir('controllers', 'Mage_Checkout')
    . DS . 'OnepageController.php';

/**
 * Handle giftcard requests
 */
class SDM_Valutec_GiftcardController
    extends Mage_Checkout_OnepageController
{
    /**
     * Apply the giftcard to the quote
     *
     * @return void
     */
    public function applyAction()
    {
        try {
            $payment = $this->getRequest()->getPost('payment');
            $balance = $this->getBalance($payment);
            if ($balance > 0) {
                $quote = Mage::getSingleton('checkout/session')->getQuote();
                $quote->setSdmValutecGiftcard(Mage::helper('core')->jsonEncode(array(
                        'number'  => $payment['card_number'],
                        'pin'     => $payment['card_pin'],
                        'balance' => $balance,
                    )))
                    ->collectTotals()
                    ->save();
                $response = array(
                    'update_section' => array(
                        'name' => 'payment-method',
                        'html' => $this->_getUpdatedPaymentMethodsHtml($this->__(
                            '%s has been applied to your order.  Remaining payment due: %s
<a type="button" class="button" onclick="sdm_valutec_giftcard.remove()" style="float: right" id="sdm-valutec-giftcard-remove-button-inmessage">
    <span>
        <span>Remove</span>
    </span>
</a>
<div style="clear: both"></div>',
                            Mage::helper('core')->currency($quote->getSdmValutecGiftcardAmount(), true, false),
                            Mage::helper('core')->currency($quote->getGrandTotal(), true, false)
                        ))
                    )
                );
            } else {
                $response = array(
                    'message' => $this->__('This card has insufficient funds. It can\'t be applied.')
                );
            }
        } catch (SDM_Valutec_Exception $e) {
            $response = array(
                'message' => $this->getErrorMessage($e->getMessage())
            );
        } catch (Exception $e) {
            Mage::logException($e);
            $response = array(
                'message' => $e->getMessage()
            );
        }
        $this->sendJsonResponse($response);
    }

    /**
     * Check a giftcard balance
     *
     * @return void
     */
    public function balanceAction()
    {
        try {
            $message = $this->__(
                'Balance: %s',
                Mage::helper('core')->currency(
                    $this->getBalance($this->getRequest()->getPost('payment')),
                    true,
                    false
                )
            );
        } catch (SDM_Valutec_Exception $e) {
            $message = $this->getErrorMessage($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $message = $e->getMessage();
        }
        $this->sendJsonResponse(array(
            'message' => $message
        ));
    }

    /**
     * Remove the giftcard from the quote
     *
     * @return void
     */
    public function removeAction()
    {
        try {
            Mage::getSingleton('checkout/session')->getQuote()
                ->setSdmValutecGiftcard(null)
                ->setBaseSdmValutecGiftcardAmount(null)
                ->setSdmValutecGiftcardAmount(null)
                ->collectTotals()
                ->save();
            $response = array(
                'update_section' => array(
                    'name' => 'payment-method',
                    'html' => $this->_getUpdatedPaymentMethodsHtml($this->__(
                        'The giftcard has been removed.'
                    ))
                )
            );
        } catch (SDM_Valutec_Exception $e) {
            $response = array(
                'message' => $this->getErrorMessage($e->getMessage())
            );
        } catch (Exception $e) {
            Mage::logException($e);
            $response = array(
                'message' => $e->getMessage()
            );
        }
        $this->sendJsonResponse($response);
    }

    /**
     * The the balance based on the submitted payment data
     *
     * @param  array $payment
     * @return double
     */
    public function getBalance(array $payment)
    {
        return Mage::getSingleton('sdm_valutec/api_transaction')
            ->balance(
                isset($payment['card_number']) ? $payment['card_number'] : false,
                isset($payment['card_pin'])    ? $payment['card_pin']    : false,
                SDM_Valutec_Model_Api_Transaction::BALANCE_TYPE_GIFT
            );
    }

    /**
     * Send a json encoded response
     *
     * @param array $response
     *
     * @return void
     */
    public function sendJsonResponse(array $response)
    {
        $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ->setBody(Mage::helper('core')->jsonEncode($response));
    }

    /**
     * Get the payment form block and add an optional message
     *
     * @param  string|boolean $message
     * @return string
     */
    protected function _getUpdatedPaymentMethodsHtml($message = false)
    {
        $html = '';
        if ($message) {
            $html .= <<<HTML
<div class="sdm-valutech-giftcard-payment-message">{$message}</div>
<script>
$$('#checkout-payment-method-load dt').each(function(dt) {
    if (dt.select('input').first().value != 'sdm_valutech_giftcard') {
        dt.select('input').first().click();
        throw \$break;
    }
});
</script>
HTML;
        }
        $html .= parent::_getPaymentMethodsHtml();
        return $html;
    }

    /**
     * Return a friendly error message for known error responses
     *
     * @param  string $response
     * @return string
     */
    public function getErrorMessage($response = 'Unknown')
    {
        switch ($response) {
            case 'CANNOT ACCEPT CARD':
                return $this->__('The number and pin don\'t seem to match.  Please check your entry and try again.');
            case 'CARD DEACTIVATED':
                return $this->__('This card is deactivated and can\'t be used.');
            case 'CARD NOT ACTIVE':
                return $this->__('This card is not active and can\'t be used.');
        }
        return $this->__('Error: %s', $response);
    }
}
