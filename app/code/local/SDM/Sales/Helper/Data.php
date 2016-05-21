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
 * SDM_Sales_Helper_Data class
 */
class SDM_Sales_Helper_Data extends SDM_Core_Helper_Data
{
    /**
     * Custom order status labels and codes
     */
    const ORDER_STATUS_LABEL_PENDING = 'Pending';
    const ORDER_STATUS_LABEL_PROCESSING = 'Processing';
    const ORDER_STATUS_LABEL_NEW = 'New';
    const ORDER_STATUS_LABEL_OPEN = 'Open';
    const ORDER_STATUS_LABEL_INPROCESS = 'In Process';
    const ORDER_STATUS_LABEL_SHIPPED = 'Shipped';

    const ORDER_STATUS_CODE_PENDING = 'pending';
    const ORDER_STATUS_CODE_PROCESSING = 'processing';
    const ORDER_STATUS_CODE_NEW = 'new';
    const ORDER_STATUS_CODE_OPEN = 'open';
    const ORDER_STATUS_CODE_INPROCESS = 'inprocess';
    const ORDER_STATUS_CODE_SHIPPED = 'shipped';

    /**
     * Payment method code from the order payment column
     */
    const PAYMENT_TYPE_CODE_SAGEPAY = 'sagepaydirectpro';
    const PAYMENT_TYPE_CODE_CYBERSOURCE = 'cybersource_soap';
    const PAYMENT_TYPE_CODE_CYBERSOURCE_SFC = 'sfc_cybersource';
    const PAYMENT_TYPE_CODE_PAYPAL = 'paypal_express';
    const PAYMENT_TYPE_CODE_PURCHASE_ORDER = 'purchaseorder';
    const PAYMENT_TYPE_CODE_GIFTCARD = 'giftcard';
    const PAYMENT_TYPE_CODE_FREE = 'free';

    /**
     * Store ID to website code mapping
     *
     * @var array
     */
    protected $_storeIdToWebsiteCode = null;

    /**
     * Core log file
     *
     * @var string
     */
    protected $_logFile = 'sdm_sales.log';

    /**
     * Initialize
     */
    public function __construct()
    {
        $this->_storeIdToWebsiteCode = Mage::helper('sdm_core')
            ->getStoreIdsToWebsiteCodes();
    }

    /**
     * Returns all of the payments used specific to each website
     *
     * ERUS: Purchase order only, Cybersource only, or free (print catalog)
     * EEUS: Purchase order only, Cybersource and/or GC, or free (print catalog)
     * SZUS: Combination of Cybersource, giftcard, and/or PayPal Credit
     * SZUK: Sage Pay only
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    public function getPaymentsUsed($order)
    {
        $used = array();

        $websiteCode = $this->_storeIdToWebsiteCode[$order->getStoreId()];
        // $websiteCode = Mage::getModel('core/store')->load($order->getStoreId())
        //     ->getCode();

        // ERUS, EEUS orders always have only Cybersource used
        if ($websiteCode === SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_RE) {
            if ($this->isFreePaymentUsed($order)) {
                $used[] = self::PAYMENT_TYPE_CODE_FREE;
            } elseif ($this->isPurchaseOrderUsed($order)) {
                $used[] = self::PAYMENT_TYPE_CODE_PURCHASE_ORDER;
            } elseif ($this->isCybersourceUsed($order)) {
                $used[] = self::PAYMENT_TYPE_CODE_CYBERSOURCE;
            }

        } elseif ($websiteCode === SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_ED) {
            if ($this->isFreePaymentUsed($order)) {
                $used[] = self::PAYMENT_TYPE_CODE_FREE;
            } elseif ($this->isPurchaseOrderUsed($order)) {
                $used[] = self::PAYMENT_TYPE_CODE_PURCHASE_ORDER;
            } else {
                if ($this->isCybersourceUsed($order)) {
                    $used[] = self::PAYMENT_TYPE_CODE_CYBERSOURCE;
                }
                if ($this->isGiftCardUsed($order)) {
                    $used[] = self::PAYMENT_TYPE_CODE_GIFTCARD;
                }
            }

        } elseif ($websiteCode === SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_US) {
            if ($this->isCybersourceUsed($order)) {
                $used[] = self::PAYMENT_TYPE_CODE_CYBERSOURCE;
            }
            if ($this->isGiftCardUsed($order)) {
                $used[] = self::PAYMENT_TYPE_CODE_GIFTCARD;
            }
            if ($this->isPaypalUsed($order)) {
                $used[] = self::PAYMENT_TYPE_CODE_PAYPAL;
            }

        } elseif ($websiteCode === SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_UK) {
            if ($this->isSagePayUsed($order)) {
                $used[] = self::PAYMENT_TYPE_CODE_SAGEPAY;
            }
        }

        return $used;
    }

    /**
     * Checks if there a machine in the order
     *
     * Option ID 33 -> Bundle, ID 158 -> Machine
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool
     */
    public function hasMachine($order)
    {
        foreach ($order->getAllVisibleItems() as $item) {
            if ($item->getItemProductType() == 33 || $item->getItemProductType() == 158) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks order was free
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool
     */
    protected function isFreePaymentUsed($order)
    {
        $method = $order->getPayment()->getMethod();
        $card = Mage::helper('sdm_valutec')->getGiftcard($order);

        // Note that giftcard-paid orders have also have "free" as payment method
        if ($method === self::PAYMENT_TYPE_CODE_FREE && !$card) {
            return true;
        }

        return false;
    }

    /**
     * Checks if Cybersource payment was used is used
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool
     */
    protected function isCybersourceUsed($order)
    {
        $method = $order->getPayment()->getMethod();

        if ($method === self::PAYMENT_TYPE_CODE_CYBERSOURCE
            || $method === self::PAYMENT_TYPE_CODE_CYBERSOURCE_SFC
        ) {
            return true;
        }

        return false;
    }

    /**
     * Checks if Cybersource payment was used is used
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool
     */
    protected function isSagePayUsed($order)
    {
        $method = $order->getPayment()->getMethod();

        if ($method === self::PAYMENT_TYPE_CODE_SAGEPAY) {
            return true;
        }

        return false;
    }

    /**
     * Checks if Valutec giftcard is used
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool
     */
    protected function isGiftCardUsed($order)
    {
        $card = Mage::helper('sdm_valutec')->getGiftcard($order);

        if ($card) {
            return true;
        }

        return false;
    }

    /**
     * Checks if PayPal Credit is used
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool
     */
    protected function isPaypalUsed($order)
    {
        $method = $order->getPayment()->getMethod();

        if ($method === self::PAYMENT_TYPE_CODE_PAYPAL) {
            return true;
        }

        return false;
    }

    /**
     * Checks if purchase order was used
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool
     */
    protected function isPurchaseOrderUsed($order)
    {
        $method = $order->getPayment()->getMethod();

        if ($method === self::PAYMENT_TYPE_CODE_PURCHASE_ORDER) {
            return true;
        }
        return false;
    }
}
