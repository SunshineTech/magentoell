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
 * SDM_Checkout_Model_Session class
 */
class SDM_Checkout_Model_Session extends Mage_Checkout_Model_Session
{
    /**
     * Get checkout quote instance by current session
     *
     * Rewritten to put a break into the endless loop caused by the coupon
     * applicatino check. SEe ELSN-326 for details.
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        Mage::dispatchEvent('custom_quote_process', array('checkout_session' => $this));

        if ($this->_quote === null) {
            /**
             * @var $quote Mage_Sales_Model_Quote
             */
            $quote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore()->getId());
            if ($this->getQuoteId()) {
                if ($this->_loadInactive) {
                    $quote->load($this->getQuoteId());
                } else {
                    $quote->loadActive($this->getQuoteId());
                }
                if ($quote->getId()) {
                    /**
                     * If current currency code of quote is not equal current currency code of store,
                     * need recalculate totals of quote. It is possible if customer use currency switcher or
                     * store switcher.
                     */
                    if ($quote->getQuoteCurrencyCode() != Mage::app()->getStore()->getCurrentCurrencyCode()) {
                        // Initialize count
                        if (is_null(Mage::registry('quote_currency_change_count'))) {
                            Mage::register('quote_currency_change_count', 1);
                        }

                        if (Mage::registry('quote_currency_change_count') <= 1) {
                            // Limit the quote totals calculation
                            $currentCount = Mage::registry('quote_currency_change_count') + 1;
                            Mage::unregister('quote_currency_change_count');
                            Mage::register('quote_currency_change_count', $currentCount);

                            $quote->setStore(Mage::app()->getStore());
                            $quote->collectTotals()->save();
                            /*
                             * We must to create new quote object, because collectTotals()
                             * can to create links with other objects.
                             */
                            $quote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore()->getId());
                            $quote->load($this->getQuoteId());
                        }
                    }
                } else {
                    $this->setQuoteId(null);
                }
            }

            $customerSession = Mage::getSingleton('customer/session');

            if (!$this->getQuoteId()) {
                if ($customerSession->isLoggedIn() || $this->_customer) {
                    $customer = ($this->_customer) ? $this->_customer : $customerSession->getCustomer();
                    $quote->loadByCustomer($customer);
                    $this->setQuoteId($quote->getId());
                } else {
                    $quote->setIsCheckoutCart(true);
                    Mage::dispatchEvent('checkout_quote_init', array('quote'=>$quote));
                }
            }

            if ($this->getQuoteId()) {
                if ($customerSession->isLoggedIn() || $this->_customer) {
                    $customer = ($this->_customer) ? $this->_customer : $customerSession->getCustomer();
                    $quote->setCustomer($customer);
                }
            }

            $quote->setStore(Mage::app()->getStore());
            $this->_quote = $quote;
        }

        if ($remoteAddr = Mage::helper('core/http')->getRemoteAddr()) {
            $this->_quote->setRemoteIp($remoteAddr);
            $xForwardIp = Mage::app()->getRequest()->getServer('HTTP_X_FORWARDED_FOR');
            $this->_quote->setXForwardedFor($xForwardIp);
        }

        // if ($this->_quote->getQuoteCurrencyCode() != Mage::app()->getStore()->getCurrentCurrencyCode()) {
        //     Mage::log('Re-collectin totals on cart');
        //     $this->_quote->collectTotals()->save();
        // }

        return $this->_quote;
    }
}
