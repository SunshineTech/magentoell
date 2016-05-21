<?php
/**
 * Separation Degrees One
 *
 * Pre Order Module
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_PreOrder
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Pre order observer
 */
class SDM_PreOrder_Model_Observer
{
    /**
     * Record if item is pre-order in quote item table
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function quoteItemAdd(Varien_Event_Observer $observer)
    {
        $quoteItem = $observer->getQuoteItem();
        $product   = Mage::getModel('catalog/product')->load($observer->getProduct()->getId());
        $value     = $product->isPreorderable();
        if ($value) {
            $quoteItem->setIsPreOrder($value);
        }
        $value = $product->getReleaseDate();
        if ($value) {
            $quoteItem->setPreOrderReleaseDate($value);
        }
    }

    /**
     * Save a quote item's release date to product_options so it will show
     * in the order item grid of the admin
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function quoteItemConvert(Varien_Event_Observer $observer)
    {
        $quoteItem = $observer->getItem();
        if (!$quoteItem->getIsPreOrder()) {
            return;
        }
        $orderItem = $observer->getOrderItem();
        $options   = $orderItem->getProductOptions();
        if (!isset($options['attributes_info'])) {
            $options['attributes_info'] = array();
        }
        $date = $quoteItem->getPreOrderReleaseDate();
        if ($date) {
            $options['attributes_info'][] = array(
                'label' => Mage::helper('sdm_preorder')->__('Release Date'),
                'value' => Mage::getSingleton('core/date')->gmtDate('n/d/Y', $date)
            );
        }
        $date = $quoteItem->getPreOrderShippingDate();
        $options['attributes_info'][] = array(
            'label' => Mage::helper('sdm_preorder')->__('Shipping Date'),
            'value' => Mage::getSingleton('core/date')->gmtDate('n/d/Y', $date)
        );
        $orderItem->setProductOptions($options);
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
    public function checkoutAllowed(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('sdm_preorder')->isQuotePreOrder()) {
            return;
        }
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $items = $quote->getAllVisibleItems();
        $itemCount = count($items);
        $approvedItems = 0;
        foreach ($quote->getAllVisibleItems() as $item) {
            $approvedItems += $item->getPreOrderApproved();
        }
        if ($itemCount == $approvedItems) {
            return;
        }
        Mage::getSingleton('checkout/session')->addError(Mage::helper('sdm_preorder')->__(
            'You have pre order items in your cart that are not yet available.'
                . '  Please click the "Pre-Order" button to save your order.'
                . '  You will be notified once the items are released and ready for purchase.'
        ));
        Mage::app()->getResponse()->setRedirect(Mage::getUrl('checkout/cart'))
            ->sendResponse();
        exit;
    }
}
