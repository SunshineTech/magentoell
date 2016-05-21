<?php
/**
 * Separation Degrees One
 *
 * Implements the customer/retailer discount logic for viewing and obtaining
 * discounts and prices, as wel as managing the customer groups.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CustomerDiscount
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_CustomerDiscount_Model_Observer class
 */
class SDM_CustomerDiscount_Model_Observer
{
    /**
     * Array to hold price data (regular, catalog-discounted, and
     * coupon-discounted prices) of quote items.
     *
     * @var array
     */
    protected $_finalPrices = array();

    /**
     * Compare all of the catalog retailer prices to the final price and replace
     * the final price with the lowest price. Applies to all websites but only
     * retailer sites compare all prices. Other websites only compare final
     * and promo prices.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function applyPriceComparison($observer)
    {
        // If checking out saved quote, do not apply price comparison
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        if (strpos($currentUrl, '/checkout/onepage') !== false
            && Mage::helper('savedquote')->isSavedQuoteSession()
        ) {
            return;
        }

        // Check product
        $product = $observer->getProduct();

        if (!$product
            || $product->isPrintCatalog()
            || $product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_SIMPLE
        ) {
            return;
        }
        $websiteCode = Mage::app()->getWebsite()->getCode();
        $storeCode = Mage::app()->getStore()->getCode();

        // Customer needs to be checked to make sure he is logged in
        $customerSession = Mage::getSingleton('customer/session');
        if (!is_null($customerSession) && $customerSession->isLoggedIn()) {
            $customerId = $customerSession->getCustomerId();
        } else {
            $customerId = 0;    // Not logged in customer ID
        }

        $finalPrice = $product->getFinalPrice();

        if (!$finalPrice) {
            // ELSN-795: Root cause could not be identified. Possibly related to ELSN-856 as well.
            // Mage::helper('customerdiscount')->log(
            //     'Final price was not set in SDM_CustomerDiscount_Model_Observer::applyPriceComparison'
            //          . ' This must be investigated! Price comparison skipped. '
            //          . "{$product->getSku()} | $websiteCode | $storeCode | $currentUrl",
            //      Zend_Log::ERR
            // );
            return;
        }

        // Promotion (if specified in it) and special prices apply to all stores
        $promoPrice = Mage::helper('customerdiscount/price')->getPromoPrice(
            $product
        );
        if (!$promoPrice) {
            $promoPrice = SDM_CustomerDiscount_Helper_Price::VERY_BIG_NUMBER;
        }

        // UK Euro store has its own special price
        if ($storeCode == SDM_Core_Helper_Data::STORE_CODE_UK_EU) {
            $specialPrice = $product->getSpecialPriceEuro();
        } else {
            $specialPrice = $product->getSpecialPrice();
        }
        if (!$specialPrice) {
            $specialPrice = SDM_CustomerDiscount_Helper_Price::VERY_BIG_NUMBER;
        }

        // These prices are only for the retailer website
        if ($websiteCode == SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_RE) {
            // Retailer discount (depends on customer group and product discount category)
            $retailerPrice = Mage::helper('customerdiscount/price')->getRetailerPrice(
                $product,
                $customerId
            );

            // Get negotiated product prices, if available
            $negotiatedPrice = Mage::helper('customerdiscount/price')->getNegotiatedPrice(
                $product,
                $customerId
            );

            $lowestPrice = min( // Take the smallest of the catalog prices
                $retailerPrice,
                $negotiatedPrice,
                // $specialPrice,   // should not be included bc it has a date range
                $promoPrice,
                $finalPrice
            );
        } else {
            $lowestPrice = min(
                // $specialPrice,
                $promoPrice,
                $finalPrice
            );
            // Mage::log("Promo: $promoPrice"); Mage::log("finalPrice: $finalPrice");
        }

        $product->setFinalPrice($lowestPrice);

        /**
         * Flag the type of discount applied. Visible only to product view pages.
         * Set special_price flag first, as specified by Ellison.
         */
        $discountTypeApplied = null;
        if ($lowestPrice == $specialPrice && $finalPrice == $specialPrice) {
            // Mage::log("Special: $lowestPrice == $specialPrice && $finalPrice == $specialPrice");
            $discountTypeApplied = SDM_Catalog_Helper_Data::DISCOUNT_TYPE_APPLIED_CODE_SPECIAL_PRICE;
        } elseif ($lowestPrice == $promoPrice && $finalPrice > $promoPrice) {
            // Mage::log("Promo: $lowestPrice == $promoPrice && $finalPrice > $promoPrice");
            $discountTypeApplied = SDM_Catalog_Helper_Data::DISCOUNT_TYPE_APPLIED_CODE_PROMO;
        }
        if ($discountTypeApplied) {
            $product->setDiscountTypeApplied($discountTypeApplied);
        }

        // Mage::log("Final Price After: {$product->getFinalPrice()} | {$product->getSku()}");
        // Mage::log(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10));

        // Cache these prices for coupon application later
        $this->_finalPrices[$product->getId()]['regular'] = $product->getPrice();  // cache for next observer
        $this->_finalPrices[$product->getId()]['catalog'] = $lowestPrice;
    }

    /**
     * After a coupon is removed, check for any custom price overrides that
     * happened when coupon discount superseded catalog discount and reset it
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function resetQuoteItemPrice($observer)
    {
        // Get controller action
        $controller = $observer->getControllerAction();

        // @see Mage_Checkout_CartController::couponPostAction
        if ($controller->getRequest()->getParam('remove') == 1) {
            // No coupon; need to reset custom price
            $items = $this->_getQuote()->getAllVisibleItems();

            foreach ($items as $item) {
                $item->setOriginalCustomPrice(null)
                    ->setCustomPrice(null)
                    ->save();
            }
        }
    }

    /**
     * Check if coupon-discounted price is better than catalog-discounted price
     * and adjust coupon application accordingly.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function getCouponDiscount($observer)
    {
        // Break out of an infinite quote recollect issue
        if (Mage::registry('quote_recollect_triggered') > 1) {
            return;
        }

        $quote = $this->_getQuote();
        $couponCode = $quote->getCouponCode();
        $item = $observer->getItem();
        if (!$couponCode || !$item) {
            return;
        }

        $item->setCustomPrice(null)
            ->setOriginalCustomPrice(null);

        // Calculate coupon-discounted item price
        $regularPrice = $item->getMsrp();
        $discountAmount = abs($item->getDiscountAmount());  // Off of MSRP
        if ($discountAmount > 0) {
            $discountPerItem = (double)($discountAmount/$item->getQty());
            $couponDiscountedPrice = round($regularPrice - $discountPerItem, 2);
            $this->_finalPrices[$item->getProductId()]['cart'] = $couponDiscountedPrice;
        } else {
            return;     // Nothing more to do if coupon discount is 0
        }

        //Compare to previously applied final price
        if (isset($this->_finalPrices[$item->getProductId()])) {
            if ((double)$this->_finalPrices[$item->getProductId()]['catalog'] <= (double)$couponDiscountedPrice
                && isset($this->_finalPrices[$item->getProductId()]['cart'])
            ) {
                // Undo what the SalesRule validator processed, so the discount
                // does not apply/get collected
                $item->setDiscountAmount(0)
                    ->setBaseDiscountAmount(0)
                    ->setOriginalDiscountAmount(0)
                    ->setBaseOriginalDiscountAmount(0);

            } else {
                // Recalculate totals
                if (isset($this->_finalPrices[$item->getProductId()])) {
                    $this->_updateCustomPrice($quote, $item, $this->_finalPrices[$item->getProductId()]);

                    // Add affected SKU
                    $skus = $quote->getAffectedSku();
                    if ($skus) {
                        $skus[$item->getSku()] = $item->getSku();
                    } else {
                        $skus = array($item->getSku() => $item->getSku());
                    }
                    $quote->setAffectedSku($skus);

                } else {
                    $this->_removeCoupon($quote);
                    Mage::getSingleton('checkout/session')->addError(
                        Mage::helper('customerdiscount')->__(
                            "Coupon \"%s\" cannot be applied",
                            $couponCode
                        )
                    );
                }
            }

        } else {
            $this->_removeCoupon($quote);
            Mage::getSingleton('checkout/session')->addError(
                Mage::helper('customerdiscount')->__(
                    "Coupon \"%s\" cannot be applied",
                    $couponCode
                )
            );
        }
    }

    /**
     * Adjusts the custom prices to allow coupon application to have the proper
     * effect.
     *
     * @param Mage_Sales_Model_Quote      $quote
     * @param Mage_Sales_Model_Quote_Item $item
     * @param array                       $priceData
     *
     * @return void
     */
    protected function _updateCustomPrice($quote, $item, $priceData)
    {
        $item->setCustomPrice($priceData['regular'])
            ->setOriginalCustomPrice($priceData['regular'])
            ->setCustomPriceFlag(true); // Flag could be used in template for styling
    }

    /**
     * Removes the coupon from the quote
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return void
     */
    protected function _removeCoupon($quote)
    {
        $quote->setCouponCode('')
            ->setAffectedSku(null)
            ->save();
    }

    /**
     * Returns the current quote from the session.
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        // Getting quote can be done using a singleton
        return Mage::getSingleton('checkout/cart')->getQuote();
    }
}
