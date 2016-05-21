<?php
/**
 * Separation Degrees Media
 *
 * Allows saving quotes that can be later be converted into orders with preserved
 * pricing.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_SavedQuote
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * Quote submit service model
 */
class SDM_SavedQuote_Model_Service_Quote
    extends Mage_Sales_Model_Service_Quote
{

    /**
     * Submit the quote. Quote submit process will create the order based on quote data
     *
     * 1. Added quote reset before and after validation
     *
     * @return Mage_Sales_Model_Order
     */
    public function submitOrder()
    {
        // If not saved quote, then fall back to parent behavior
        if (!Mage::helper('savedquote')->isSavedQuoteSession()) {
            return parent::submitOrder();
        }

        // Resetting quote does not seem necessary and removes payment info
        // Reset quote data before validation...
        // Mage::helper('savedquote')->resetQuoteDataFromSavedQuote();

        $this->_deleteNominalItems();
        $this->_validate();
        $quote = $this->_quote;
        $isVirtual = $quote->isVirtual();

        // Reset quote data after validation...
        // Mage::helper('savedquote')->resetQuoteDataFromSavedQuote();

        $transaction = Mage::getModel('core/resource_transaction');
        if ($quote->getCustomerId()) {
            $transaction->addObject($quote->getCustomer());
        }
        $transaction->addObject($quote);

        $quote->reserveOrderId();
        if ($isVirtual) {
            $order = $this->_convertor->addressToOrder($quote->getBillingAddress());
        } else {
            $order = $this->_convertor->addressToOrder($quote->getShippingAddress());
        }
        $order->setBillingAddress($this->_convertor->addressToOrderAddress($quote->getBillingAddress()));
        if ($quote->getBillingAddress()->getCustomerAddress()) {
            $order->getBillingAddress()->setCustomerAddress($quote->getBillingAddress()->getCustomerAddress());
        }
        if (!$isVirtual) {
            $order->setShippingAddress($this->_convertor->addressToOrderAddress($quote->getShippingAddress()));
            if ($quote->getShippingAddress()->getCustomerAddress()) {
                $order->getShippingAddress()->setCustomerAddress($quote->getShippingAddress()->getCustomerAddress());
            }
        }
        $order->setPayment($this->_convertor->paymentToOrderPayment($quote->getPayment()));

        foreach ($this->_orderData as $key => $value) {
            $order->setData($key, $value);
        }

        foreach ($quote->getAllItems() as $item) {
            $orderItem = $this->_convertor->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $order->addItem($orderItem);
        }

        $order->setQuote($quote);
        // SDM_SavedQuote_Helper_Data::isSavedQuoteSession takes care of flagging
        // $order->setIsFromSavedQuote(true);

        $transaction->addObject($order);
        $transaction->addCommitCallback(array($order, 'place'));
        $transaction->addCommitCallback(array($order, 'save'));

        /**
         * We can use configuration data for declare new order status
         */
        Mage::dispatchEvent('checkout_type_onepage_save_order', array('order'=>$order, 'quote'=>$quote));
        Mage::dispatchEvent('sales_model_service_quote_submit_before', array('order'=>$order, 'quote'=>$quote));
        try {
            $transaction->save();
            $this->_inactivateQuote();
            Mage::dispatchEvent('sales_model_service_quote_submit_success', array('order'=>$order, 'quote'=>$quote));
        } catch (Exception $e) {
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                // reset customer ID's on exception, because customer not saved
                $quote->getCustomer()->setId(null);
            }

            //reset order ID's on exception, because order not saved
            $order->setId(null);
            /**
 * @var $item Mage_Sales_Model_Order_Item
*/
            foreach ($order->getItemsCollection() as $item) {
                $item->setOrderId(null);
                $item->setItemId(null);
            }

            Mage::dispatchEvent('sales_model_service_quote_submit_failure', array('order'=>$order, 'quote'=>$quote));
            throw $e;
        }
        Mage::dispatchEvent('sales_model_service_quote_submit_after', array('order'=>$order, 'quote'=>$quote));
        $this->_order = $order;
        return $order;
    }

    /**
     * Validate quote data before converting to order
     *
     * 1. Skipped validating shipping method
     *
     * @return Mage_Sales_Model_Service_Quote
     */
    protected function _validate()
    {
        // If not saved quote, then fall back to parent behavior
        if (!Mage::helper('savedquote')->isSavedQuoteSession()) {
            return parent::_validate();
        }

        if (!$this->getQuote()->isVirtual()) {
            $address = $this->getQuote()->getShippingAddress();
            $addressValidation = $address->validate();
            if ($addressValidation !== true) {
                Mage::throwException(
                    Mage::helper('sales')->__('There is an unrecoverable issue with your shipping address. '
                        . 'Please contact our support team for assistance with completing this quote.')
                );
            }
            // Skip validating shipping method
            // $method= $address->getShippingMethod();
            // $rate  = $address->getShippingRateByCode($method);
            // if (!$this->getQuote()->isVirtual() && (!$method || !$rate)) {
            //     Mage::throwException(Mage::helper('sales')->__('Please specify a shipping method.'));
            // }
        }

        $addressValidation = $this->getQuote()->getBillingAddress()->validate();
        if ($addressValidation !== true) {
            Mage::throwException(
                Mage::helper('sales')->__(
                    'Please check billing address information. %s',
                    implode(' ', $addressValidation)
                )
            );
        }

        if (!($this->getQuote()->getPayment()->getMethod())) {
            Mage::throwException(Mage::helper('sales')->__('Please select a valid payment method.'));
        }

        return $this;
    }
}
