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
 * SDM_Checkout_Model_Observer class
 */
class SDM_Checkout_Model_Observer
{
    /**
     * Checks the quote items when a cart update is performed and adjusts the
     * quantity as necessary according to the minimum requirement. Skips qty
     * check if customer group allows it.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Varien_Event_Observer
     */
    public function checkQtysAfterQuoteUpdate($observer)
    {
        if (Mage::app()->getWebsite()->getCode() != SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_RE) {
            return;
        }

        $cart = $observer->getCart();
        $items = $cart->getItems();
        if (!$items) {
            return; // No items so nothing to do.
        }

        // Check if customer group has min qty override flag
        $customer = $cart->getCustomerSession()->getCustomer();
        if (Mage::helper('sdm_checkout')->canCustomerGroupOverrideMinQty($customer)) {
            return;
        }

        // Check all items for min qty requirement
        foreach ($items as $item) {
            $qty = $item->getQty();
            $minQty = $item->getProduct()->getMinQty();  // Missing min_qty
            if (is_null($minQty) || $minQty === false) {    // Load directly if N/A
                $minQty = Mage::getModel('catalog/product')
                    ->load($item->getProduct()->getId())
                    ->getMinQty();
            }

            if ($minQty > $qty) {
                $item->setQty($minQty);

                Mage::getSingleton('checkout/session')
                    ->addWarning(
                        Mage::helper('customerdiscount')->__(
                            "Minimum purchase quantity for SKU '%s' is %s. Quantity updated to %s.",
                            $item->getProduct()->getSku(),
                            $minQty,
                            $minQty
                        )
                    );
            }
        }

        return $observer;
    }

    /**
     * Check that checkout is allowed with the current items in the cart
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function giftcardCheck(Varien_Event_Observer $observer)
    {
        $quote         = Mage::getSingleton('checkout/session')->getQuote();
        $items         = $quote->getAllVisibleItems();
        $itemCount     = count($items);
        $giftcardTypes = array(309, 724);
        $approvedItems = 0;
        foreach ($quote->getAllVisibleItems() as $item) {
            if (in_array($item->getProduct()->getProductType(), $giftcardTypes)) {
                $approvedItems++;
            }
        }
        if ($approvedItems == 0 || $itemCount == $approvedItems) {
            return;
        }
        Mage::getSingleton('checkout/session')->addError(Mage::helper('catalog')->__(
            '<strong>Please note</strong>: Your shopping cart contains one or more Gift Cards. Gift Cards must be '
                . 'ordered separately from other items. Please remove items that cannot be ordered together and '
                . 'proceed to checkout. Remember, Gift Card orders ship for FREE!'
        ));
        Mage::app()->getResponse()->setRedirect(Mage::getUrl('checkout/cart'))
            ->sendResponse();
        exit;
    }
}
