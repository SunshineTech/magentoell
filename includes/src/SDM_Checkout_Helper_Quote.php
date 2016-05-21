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
 * SDM_Checkout_Helper_Quote class
 */
class SDM_Checkout_Helper_Quote extends SDM_Core_Helper_Data
{
    /**
     * Ensures that the quote is valid on the cart page.
     * Used to validate saved quotes and preorders before placement.
     *
     * @param  bool $removeItems
     * @return bool
     */
    public function validateForCart($removeItems = true)
    {
        return $this->_validate('cart', $removeItems);
    }

    /**
     * Ensures that the quote is valid on the checkout page.
     *
     * @param  bool $removeItems
     * @return bool
     */
    public function validateForCheckout($removeItems = false)
    {
        // First check and verify allow_cart
        $cartValidate = $this->_validate('cart', true);

        // If that passed, verify allow_checkout
        return $cartValidate ? $this->_validate('checkout', $removeItems) : false;
    }

    /**
     * Validates the quote item quantities against the live inventory in order to
     * ensure correct life cycle application to checkout.
     *
     * @param  string $type        What are we validating this for?
     * @param  bool   $removeItems Remove items from quote?
     * @return bool
     */
    protected function _validate($type = 'checkout', $removeItems = true)
    {
        $quote = $this->_getQuote();
        $quoteItems = $quote->getAllVisibleItems();
        $removedItems = array();
        $stockMessages = array();
        $quoteValidated = true;
        $isPreOrder = (bool)Mage::helper('savedquote')->isQuotePreOrder($quote);
        $approvedOnlyVal = SDM_Catalog_Model_Attribute_Source_Allowcheckout::VALUE_APPROVED_ONLY;

        // Type must equal 'cart' or 'checkout' since it's used getData() several times
        $type = $type === 'cart' ? 'cart' : 'checkout';

        foreach ($quoteItems as $item) {
            $product = Mage::getModel('catalog/product')->load($item->getData('product_id'));

            // Don't worry about print catalogs
            if ($product->isPrintCatalog()) {
                continue;
            }

            // Check allow_cart or allow_checkout
            $isAllowed = true;
            if ($type === 'cart') {
                // Cart check is easy; jsut treat allow_cart as a boolean.
                $isAllowed = (bool)$product->getData('allow_cart');
            } else {
                // Allow checkout is more complex. Check for VALUE_APPROVED_ONLY and
                // verify it's an approved preorder. Else, treat it as a boolean.
                if ((int)$product->getData('allow_checkout') === $approvedOnlyVal) {
                    $isAllowed = $isPreOrder;
                } else {
                    $isAllowed = (bool)$product->getData('allow_checkout');
                }
            }

            // Are we allowed to have this item in cart or checkout?
            if (!$isAllowed) {
                $quoteValidated = false;
                // Remove item from quote
                if ($removeItems) {
                    $quote->removeItem($item->getId())->save();
                }
                $removedItems[] = '#' . $product->getSku();
                continue; // No longer need to check quantity if this case
            }

            // Check product live quantity if backorder disabled by lifecycle
            if (!(bool)$product->getData('allow_' . $type . '_backorder')) {
                $qtyAvail = $product->getStockItem()->getQty();
                $qtyRequested = $item->getQty();

                if ($qtyAvail - $qtyRequested < 0) {
                    $quoteValidated = false;
                    $item->setQty($qtyAvail)->save();   // Adjust quote item qty
                    $stockMessages[] = Mage::helper('sdm_checkout')->__(
                        'Only %s unit(s) of #%s available. %s unit(s) were removed from your cart.',
                        (string)number_format(floor($qtyAvail)),
                        (string)$product->getSku(),
                        (string)number_format(floor($qtyRequested-$qtyAvail))
                    );
                }
            }
        }

        // Add various error messages
        if (count($removedItems)) {
            if (count($removedItems) > 1) {
                $message = Mage::helper('sdm_checkout')
                    ->__(
                        "The following products are not available at this time" .
                        ($removeItems ? " and have been removed from your cart: " : ": ")
                    );
            } else {
                $message = Mage::helper('sdm_checkout')
                    ->__(
                        "The following product is not available at this time" .
                        ($removeItems ? " and has been removed from your cart: " : ": ")
                    );
            }
            Mage::getSingleton('checkout/session')
                ->addError($message . implode(', ', $removedItems) . '.');
        }

        if (count($stockMessages)) {
            foreach ($stockMessages as $stockMessage) {
                Mage::getSingleton('checkout/session')->addError($stockMessage);
            }
        }

        if ($quoteValidated) {
            return true;
        } else {
            $quote->setTotalsCollectedFlag(false)->collectTotals()->save();
            Mage::getSingleton('checkout/session')
                ->addError('Please review your cart again before proceeding.');
            return false;
        }
    }

    /**
     * Returns the current active quote
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }
}
