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

/**
 * Handle observed events
 */
class SDM_Valutec_Model_Observer
{
    /**
     * Disable payment methods that can't be used with giftcards
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function checkPaymentMethod(Varien_Event_Observer $observer)
    {
        $quote = $observer->getQuote();
        if (!$quote) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }
        // If they're not using a giftcard we have no buisness here
        if (!$quote || $quote->getSdmValutecGiftcardAmount() <= 0) {
            return;
        }
        $methodCode = $observer->getMethodInstance()->getCode();
        $result     = $observer->getResult();
        // If the giftcard covers their entire total, only show the no payment required option
        if ($quote->getGrandTotal() == 0) {
            if ($methodCode != 'free'
                && $methodCode != 'sdm_valutech_giftcard'
            ) {
                $result->isAvailable = false;
            }
            return;
        }
        // Handle disallowed methods
        $badMethods = Mage::helper('sdm_valutec')->getDisallowedMethods();
        if (!in_array($methodCode, $badMethods)) {
            return;
        }
        $result->isAvailable = false;
    }

    /**
     * Verify the giftcard has enough funds to be used
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function orderPlaceBefore(Varien_Event_Observer $observer)
    {
        $object = $observer->getOrder();
        $card = Mage::helper('sdm_valutec')->getGiftcard($object);
        if (!$card) {
            return;
        }
        $balance = Mage::getSingleton('sdm_valutec/api_transaction')
            ->balance(
                $card['number'],
                $card['pin'],
                SDM_Valutec_Model_Api_Transaction::BALANCE_TYPE_GIFT
            );
        if ($balance < $object->getSdmValutecGiftcardAmount()) {
            throw new SDM_Valutec_Exception("This transaction cannot be completed due to insufficient funds on your gift card.  Please remove the applied Gift Card and select any other form of payment to place order. To remove the applied gift card, navigate to Payment Methods, select Gift Card Payment option and remove the applied Gift Card.");
        }
    }

    /**
     * Capture the giftcard when an order is placed
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function orderPlaceAfter(Varien_Event_Observer $observer)
    {
        $object = $observer->getOrder();
        $card  = Mage::helper('sdm_valutec')->getGiftcard($object);
        if (!$card) {
            return;
        }
        $response = Mage::getSingleton('sdm_valutec/api_transaction')
            ->sale(
                $card['number'],
                $card['pin'],
                SDM_Valutec_Model_Api_Transaction::BALANCE_TYPE_GIFT,
                $object->getBaseSdmValutecGiftcardAmount()
            );
        $card['identifier']         = $response->Identifier;
        $card['authorization_code'] = $response->AuthorizationCode;
        $object->setSdmValutecGiftcard(Mage::helper('core')->jsonEncode($card));
        $object->setSdmValutecGiftcardIdentifier($card['identifier']);
    }

    /**
     * Void the giftcard transaction when the order is cancelled
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function orderCancel(Varien_Event_Observer $observer)
    {
        $this->void($observer->getOrder());
    }

    /**
     * Void the giftcard transaction when the order is voided
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function paymentVoid(Varien_Event_Observer $observer)
    {
        $this->void($observer->getPayment()->getOrder());
    }

    /**
     * Void the giftcard transaction when the order is refunded
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function creditmemoRefund(Varien_Event_Observer $observer)
    {
        $this->void($observer->getCreditmemo()->getOrder());
    }

    /**
     * Void the giftcard transaction when the order is voided
     *
     * @param Mage_Sales_Model_Order $object
     *
     * @return void
     */
    public function void(Mage_Sales_Model_Order $object)
    {
        $card = Mage::helper('sdm_valutec')->getGiftcard($object);
        if (!$card) {
            return;
        }
        try {
            Mage::getSingleton('sdm_valutec/api_transaction')
                ->void(
                    $card['number'],
                    $card['pin'],
                    SDM_Valutec_Model_Api_Transaction::BALANCE_TYPE_GIFT,
                    $card['authorization_code']
                );
            $object->setSdmValutecGiftcardRefunded($object->getSdmValutecGiftcardAmount())
                ->setBaseSdmValutecGiftcardRefunded($object->getBaseSdmValutecGiftcardAmount())
                ->save();
            Mage::getSingleton('adminhtml/session')
                ->addSuccess('Giftcard transaction successfully voided.');
        } catch (SDM_Valutec_Exception $e) {
            if ($e->getMessage() == 'CANNOT VOID') {
                $this->_addValue($card, $object);
            } else {
                Mage::getSingleton('adminhtml/session')->addError(sprintf(
                    'Failed to void giftcard transaction (%s).  Please void manually via virtual terminal.',
                    $e->getMessage()
                ));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    /**
     * Manually add funds to a gift card when void fails
     *
     * @param array                  $card
     * @param Mage_Sales_Model_Order $order
     *
     * @return void
     */
    protected function _addValue(array $card, Mage_Sales_Model_Order $order)
    {
        try {
            Mage::getSingleton('sdm_valutec/api_transaction')
                ->addValue(
                    $card['number'],
                    $order->getBaseSdmValutecGiftcardAmount(),
                    SDM_Valutec_Model_Api_Transaction::BALANCE_TYPE_GIFT
                );
            $order->setSdmValutecGiftcardRefunded($order->getSdmValutecGiftcardAmount())
                ->setBaseSdmValutecGiftcardRefunded($order->getBaseSdmValutecGiftcardAmount())
                ->save();
            Mage::getSingleton('adminhtml/session')
                ->addSuccess('Giftcard successfully refunded.');
        } catch (SDM_Valutec_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(sprintf(
                'Failed to refund giftcard (%s).  Please refund manually via virtual terminal.',
                $e->getMessage()
            ));
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }
}
