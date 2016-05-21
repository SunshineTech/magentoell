<?php
/**
 * Separation Degrees One
 *
 * Ellison and Sizzix Shipping Rules
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Shipping
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Shipping observer
 */
class SDM_Shipping_Model_Observer
{
    /**
     * Set the surchage on the quote item
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function setQuoteItemSurcharge(Varien_Event_Observer $observer)
    {
        $quoteItem = $observer->getQuoteItem();
        $product   = Mage::getModel('catalog/product')->load($observer->getProduct()->getId());
        $fee       = (double)$product->getHandlingFee();

        if ($fee > 0) {
            $quoteItem->setSdmShippingSurcharge($fee);
            $quoteItem->setBaseSdmShippingSurcharge($fee);
        }
    }

    /**
     * Add shipping and handling charge to paypal request
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function preparePaypal(Varien_Event_Observer $observer)
    {
        /**
         * @var Mage_Paypal_Model_Cart
         */
        $cart = $observer->getEvent()->getPaypalCart();
        $handlingFee = 0;
        $entity = $cart->getSalesEntity();
        if ($entity instanceof Mage_Sales_Model_Order) {
            $entity = Mage::getModel('sales/quote')->load($entity->getQuoteId());
        }
        foreach ($entity->getAllVisibleItems() as $item) {
            $handlingFee += $item->getBaseSdmShippingSurcharge() * $item->getQty();
        }
        if ($handlingFee > 0) {
            $cart->addItem(
                'Shipping & Handling Fee',
                1,
                $handlingFee
            );
        }
    }

    /**
     * Paypal incorrectly saves data from the shipping address to the billing,
     * we need to correct that
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function unsetBillingAddressSurcharge(Varien_Event_Observer $observer)
    {
        $address = $observer->getQuoteAddress();
        if ($address->getAddressType() == 'billing' && ($address->getSdmShippingSurcharge()
            || $address->getBaseSdmShippingSurcharge())
        ) {
            $address->unsSdmShippingSurcharge()->unsBaseSdmShippingSurcharge();
        }
    }
}
