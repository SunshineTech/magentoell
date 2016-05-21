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
 * Saved quote observer
 */
class SDM_SavedQuote_Model_Observer
{
    /**
     * Apply the custom price saved in the saved quote
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Varien_Event_Observer $observer
     */
    public function productAddAfter($observer)
    {
        $item = $observer->getEvent()->getQuoteItem();

        if ($item instanceof Mage_Sales_Model_Quote_Item) {
            if ($item->getProduct()->getUseSavedPrice()) {
                $item->setCustomPrice($item->getProduct()->getCustomPrice());
                $item->setOriginalCustomPrice($item->getProduct()->getCustomPrice());
            }
        }

        return $observer;
    }

    /**
     * Before layout load
     *
     * If we have an active saved quote, this checks to make sure we're on the checkout
     * page and haven't wandered off somewhere else. If so, it removes the quote from
     * the session.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return null
     */
    public function beforeLayoutLoad($observer)
    {
        if (Mage::helper('savedquote')->isSavedQuoteSession()) {
            $controller = Mage::app()->getRequest()->getControllerName();
            $module = Mage::app()->getRequest()->getModuleName();
            if ($module !== 'checkout' || $controller === 'cart') {
                // Save checkout session messages; Unsure why they are cleared when
                // checking out a pre-order.
                $messages = Mage::helper('sdm_core')->extractSessionMessages('checkout');
                Mage::getSingleton('checkout/session')->setSessionMessages($messages);

                // Remove quote
                Mage::helper('savedquote/converter')->removeSavedQuoteFromSession();
                Mage::app()->getResponse()->setRedirect(Mage::helper('core/url')->getCurrentUrl());
                return;
            }
        }
    }

    /**
     * Runs after checkout submission
     *
     * Checks if the order was from a saved quote; if it is, leaves and comment and
     * retires the saved quote
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Varien_Event_Observer $observer
     */
    public function checkoutSubmitAllAfter($observer)
    {
        if (Mage::helper('savedquote')->isSavedQuoteSession()) {
            $event = $observer->getEvent();
            $order = $event->getOrder();
            $savedQuote = Mage::helper('savedquote')->getSavedQuote();

            // Update the saved quote
            $savedQuote->setConvertedAt(Mage::getSingleton('core/date')->gmtDate());
            $savedQuote->setOrderId($order->getId());

            // Disable saved quote
            $savedQuote->setIsActive(SDM_SavedQuote_Helper_Data::INACTIVE_FLAG)->save();

            $origQuote = Mage::getModel('sales/quote')->load($savedQuote->getQuoteId());
            if ($origQuote && $origQuote->getId()) {
                $order->setCouponCode($origQuote->getCouponCode());
            }

            // Add comment to order
            $comment = "Converted from saved quote #{$savedQuote->getIncrementId()}";
            $order->addStatusHistoryComment($comment);
            $order->save();
        }

        return $observer;
    }
}
