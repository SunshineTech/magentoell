<?php
/**
 * Separation Degrees Media
 *
 * Ellison's Mage_Sales customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Sales
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Sales_Model_Observer class
 */
class SDM_Sales_Model_Observer
{
    const PAYMENT_CODE_PURCHASE_ORDER = 'purchaseorder';
    const PAYMENT_CODE_FREE = 'free';

    /**
     * Save the custom attributes (MSRP/original price, min qty, etc.) to the
     * quote item table
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function salesQuoteItemSetCustomAttributes($observer)
    {
        $quoteItem = $observer->getQuoteItem();
        $product = $observer->getProduct();
        $store = Mage::app()->getStore();

        if (!$quoteItem || !$product) {
            return;
        }

        // "MSRP" which is just Magento's "price" attribute
        // getPrice() returns the approprirate store price
        $quoteItem->setMsrp($product->getPrice());
        // Save the integer and deligate to checking option value in order export
        $quoteItem->setItemType($product->getProductType());
        // Mage::log($product->getSku() . ': ' . $product->getProductType());
        // Mage::log($product->debug());
        // Min. qty.: opted not to save min_qty in quote_item table. Load product
        // object to get latest data
        // $quoteItem->setMinQty($product->getMinQty());
    }

    /**
     * All newly placed Ellison orders in Open/processing (status/state). This
     * includes all PayPal and gift card-only orders.
     *
     * All purchase order orders are in New/processing.
     *
     * Otherwise, all EEUS order go to New/processing.
     *
     * Default order state/status:
     * - PayPal Express: processing/inprocessing
     * - Giftcard-only ('free' payment): new/pending
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function customOrderStatusChange($observer)
    {
        $order = $observer->getOrder();
        if (!$order) {
            return;
        }
        // Mage::log('Order status: ' . $order->getStatus());
        // Mage::log('Order state: ' . $order->getState());
        // Mage::log('');

        // All EEUS orders go to "New" regardless of payment method
        $websiteCode = Mage::getModel('core/store')->load($order->getStoreId())
            ->getWebsite()->getCode();
        $paymentMethod = $order->getPayment()->getMethod();

        try {
            // All EEUS orders are in New/processing, except PO orders
            if ($websiteCode === SDM_Core_Helper_Data::WEBSITE_CODE_ED) {
                if ($paymentMethod === self::PAYMENT_CODE_PURCHASE_ORDER) {
                    return $this->_setPurchaseOrderNewOrderStatus($order);
                } else {
                    return $this->_setDefaultEeusNewOrderStatus($order);
                }

            } else {
                // All other 'processing' state orders should be in 'Open' status,
                // except for PO orders
                if ($paymentMethod == self::PAYMENT_CODE_PURCHASE_ORDER) {
                    return $this->_setPurchaseOrderNewOrderStatus($order);
                } else {
                    // This includes "free" orders (e.g. giftcard-only)
                    return $this->_setDefaultNewOrderStatus($order);
                }
            }

        } catch (Exception $e) {
            $this->log(
                'Unable to modify new order status and state. ' . $e->getMessage()
            );
        }
    }

    /**
     * Sets the default new order status and state.
     *
     * If there's a machine in the order, it gets the New/processing status/state
     * for customer service to review the order.
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return null
     */
    protected function _setDefaultNewOrderStatus($order)
    {
        // Not sure why the first if-else statement was written
        if ($order->getStatus() !== SDM_Sales_Helper_Data::ORDER_STATUS_CODE_OPEN
            || $order->getState() !== Mage_Sales_Model_Order::STATE_PROCESSING
        ) {
            $this->_correctNewOrderStatus($order);
        } else {
            $this->_correctNewOrderStatus($order);
        }
    }

    /**
     * Correct order status and state
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return void
     */
    protected function _correctNewOrderStatus($order)
    {
        if (Mage::helper('sdm_sales')->hasMachine($order)) {
            $order->setStatus(SDM_Sales_Helper_Data::ORDER_STATUS_CODE_NEW)
                ->setState(Mage_Sales_Model_Order::STATE_PROCESSING)
                ->save();

            $order->addStatusHistoryComment('Status changed to "New" for inspection.')
                ->save();
        } else {
            $order->setStatus(SDM_Sales_Helper_Data::ORDER_STATUS_CODE_OPEN)
                ->setState(Mage_Sales_Model_Order::STATE_PROCESSING)
                ->save();
        }
    }

    /**
     * Sets the default EEUS order status and state (New/processing). Assumes
     * the order is for EEUS.
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return null
     */
    protected function _setDefaultEeusNewOrderStatus($order)
    {
        if ($order->getStatus() !== SDM_Sales_Helper_Data::ORDER_STATUS_CODE_NEW
            || $order->getState() !== Mage_Sales_Model_Order::STATE_PROCESSING
        ) {
            $order->setStatus(SDM_Sales_Helper_Data::ORDER_STATUS_CODE_NEW)
                ->setState(Mage_Sales_Model_Order::STATE_PROCESSING)
                ->save();
        }
    }

    /**
     * Overrides the system configuration for PO orders and set them to
     * New/processing regardless. Assumes order passed was a PO order.
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return null
     */
    protected function _setPurchaseOrderNewOrderStatus($order)
    {
        if ($order->getStatus() !== SDM_Sales_Helper_Data::ORDER_STATUS_CODE_NEW
            || $order->getState() !== Mage_Sales_Model_Order::STATE_PROCESSING
        ) {
            $order->setStatus(SDM_Sales_Helper_Data::ORDER_STATUS_CODE_NEW)
                ->setState(Mage_Sales_Model_Order::STATE_PROCESSING)
                ->save();
        }
    }
}
