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

/**
 * Adds more methods to get order data
 */
class SDM_Checkout_Block_Onepage_Success
    extends Mage_Checkout_Block_Onepage_Success
{
    /**
     * Get last order ID from session, fetch it and check whether it can be viewed, printed etc.
     *
     * Rewritten to add more data.
     *
     * @return void
     */
    protected function _prepareLastOrder()
    {
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        if ($orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->getId()) {
                $isVisible = !in_array($order->getState(),
                    Mage::getSingleton('sales/order_config')->getInvisibleOnFrontStates());
                $this->addData(array(
                    'is_order_visible' => $isVisible,
                    'view_order_id' => $this->getUrl('sales/order/view/', array('order_id' => $orderId)),
                    'print_url' => $this->getUrl('sales/order/print', array('order_id'=> $orderId)),
                    'can_print_order' => $isVisible,
                    'can_view_order'  => Mage::getSingleton('customer/session')->isLoggedIn() && $isVisible,
                    'order_id'  => $order->getIncrementId(),
                    'entity_id'  => $order->getId(),
                    'payment_method'  => $order->getPayment()->getMethod(),
                    'order_subtotal'  => $order->getSubtotal()
                ));
            }
        }
    }

    /**
     * Show Share A Sale tracking code, but only for EEUS or SZUS
     *
     * @return bool
     */
    public function trackShareASale()
    {
        return Mage::helper('sdm_core')
            ->isSite(SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_US, SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_ED);
    }

    /**
     * Get the merchant ID for share a sale
     *
     * @return string
     */
    public function getShareASaleMerchant()
    {
        if (Mage::helper('sdm_core')->isSite(SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_US)) {
            return "51987";
        }

        if (Mage::helper('sdm_core')->isSite(SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_US)) {
            return "55564";
        }

        return '';
    }

    /**
     * Get order subtotal for tracking code
     *
     * @return float
     */
    public function getOrderSubtotal()
    {
        return number_format((float)$this->_getData('order_subtotal'), 2);
    }

    /**
     * Get order subtotal for tracking code
     *
     * @return float
     */
    public function getCustomerId()
    {
        $customerSession = Mage::getSingleton('customer/session');
        if ($customerSession->isLoggedIn()) {
            return $customerSession->getCustomer()->getId();
        }
        return 0;
    }

    /**
     * Returns the My Account link to the provided order ID
     *
     * @param integer $id
     *
     * @return str
     */
    public function getOrderUrl($id)
    {
        return Mage::getUrl('sales/order/view', array('order_id' => $id));
    }
}
