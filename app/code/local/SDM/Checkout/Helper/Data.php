<?php
/**
 * Separation Degrees One
 *
 * Checkout-related customization
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Checkout
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Checkout_Helper_Data class
 */
class SDM_Checkout_Helper_Data extends SDM_Core_Helper_Data
{
    const PURCHASE_ORDER_PAYMENT_CODE = 'purchaseorder';    // Unfortunately hard-coded originally in Mage

    /**
     * Checks to see if the given customer's customer group allows overriding
     * the minimum quantity requirement
     *
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return bool
     */
    public function canCustomerGroupOverrideMinQty($customer)
    {
        $groupId = $customer->getGroupId();
        $minQtyOverride = Mage::getModel('customer/group')->load($groupId)
            ->getMinQtyOverride();

        if ($minQtyOverride == 1) {
            return true;
        }

        return false;
    }

    /**
     * Return an array of qty and stock alert message if qty below system-configured
     * threshold. Message variations apply.
     *
     * @param Mage_Sales_Model_Quote_Item $item
     *
     * @return array
     */
    public function getStockMessage($item)
    {
        $stockData = array();
        $product = $item->getProduct();
        $qtyThreshold = Mage::getStoreConfig('cataloginventory/options/stock_threshold_qty');

        $qty = (int)Mage::getModel('cataloginventory/stock_item')
            ->loadByProduct($product)
            ->getQty();

        // Stock message not applicable to preorderable and print catalog products
        if (!$product->isPreorderable() && $qty <= $qtyThreshold && !$product->isPrintCatalog()) {
            if ($qty > 0  && $product->isBackorderable()) {
                $stockData['qty'] = $qty;
                $stockData['message'] = 'Only %s left! Accepting backorders.';
            } elseif ($qty <= 0  && $product->isBackorderable()) {
                $stockData['qty'] = '';
                $stockData['message'] = 'Out of Stock! Accepting backorders.%s';
            } elseif ($qty > 0) {
                $stockData['qty'] = $qty;
                $stockData['message'] = 'Only %s left!';
            } elseif ($qty <= 0) {
                $stockData['qty'] = '';
                $stockData['message'] = 'Out of Stock.%s';
            }
            return $stockData;
        }
        return null;
    }
}
