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
 * SDM_Sales_Model_Quote_Address_Total_Subtotal class
 */
class SDM_Sales_Model_Quote_Address_Total_Subtotal
    extends Mage_Sales_Model_Quote_Address_Total_Subtotal
{
    /**
     * Address item initialization
     *
     * Rewritten to override product's price when custom price is not available.
     * It was found that discounted saved quote items' prices were not being
     * applied correctly in the review block of Onepage checkout on the QA DB,
     * even though each line items had correct values. When custom price is not
     * available for a given Onepage step, this fix uses the line item's price
     * to overwrite the product's price, which is used in calculating the final
     * price in such case. This could not be replicated with other DBs.
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @param Mage_Sales_Model_Quote_Item    $item
     *
     * @return bool
     */
    protected function _initItem($address, $item)
    {
        if ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
            $quoteItem = $item->getAddress()->getQuote()->getItemById($item->getQuoteItemId());
        } else {
            $quoteItem = $item;
        }
        $product = $quoteItem->getProduct();
        $product->setCustomerGroupId($quoteItem->getQuote()->getCustomerGroupId());

        // Begine rewrite ****
        // URL check must be made; otherwise, item prices get discounted repeatedly
        // in the cart.
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        if ($quoteItem->getPrice() > 0 && !$quoteItem->getCustomPrice()
            && strpos($currentUrl, '/checkout/onepage/') !== false
            && Mage::helper('savedquote')->isSavedQuoteSession()
        ) {
            $product->setPrice($quoteItem->getPrice());
        }
        // End rewrite *******

        /**
         * Quote super mode flag mean what we work with quote without restriction
         */
        if ($item->getQuote()->getIsSuperMode()) {
            if (!$product) {
                return false;
            }
        } else {
            if (!$product || !$product->isVisibleInCatalog()) {
                return false;
            }
        }

        if ($quoteItem->getParentItem() && $quoteItem->isChildrenCalculated()) {
            $finalPrice = $quoteItem->getParentItem()->getProduct()->getPriceModel()->getChildFinalPrice(
               $quoteItem->getParentItem()->getProduct(),
               $quoteItem->getParentItem()->getQty(),
               $quoteItem->getProduct(),
               $quoteItem->getQty()
            );
            $item->setPrice($finalPrice)
                ->setBaseOriginalPrice($finalPrice);
            $item->calcRowTotal();
        } elseif (!$quoteItem->getParentItem()) {
            $finalPrice = $product->getFinalPrice($quoteItem->getQty());
            $item->setPrice($finalPrice)
                ->setBaseOriginalPrice($finalPrice);
            $item->calcRowTotal();
            $this->_addAmount($item->getRowTotal());
            $this->_addBaseAmount($item->getBaseRowTotal());
            $address->setTotalQty($address->getTotalQty() + $item->getQty());
        }

        return true;
    }
}
