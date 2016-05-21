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
 * SDM_Checkout_Model_Cart class
 */
class SDM_Checkout_Model_Cart extends Mage_Checkout_Model_Cart
{
    /**
     * Dispatch a checkout_cart_product_add_before event
     *
     * @param int|Mage_Catalog_Model_Product $productInfo
     * @param mixed                          $requestInfo
     *
     * @return Mage_Checkout_Model_Cart
     */
    public function addProduct($productInfo, $requestInfo = null)
    {
        $product = $this->_getProduct($productInfo);
        $request = $this->_getProductRequest($requestInfo);

        // Dispatch a custom event, but not used (might need it down the road)
        // Mage::dispatchEvent(
        //     'checkout_cart_product_add_before',
        //     array('request' => $request, 'product' => $product)
        // );

        // Check if min qty requirement is met
        $customer = $this->getCustomerSession()->getCustomer();
        if (Mage::app()->getWebsite()->getCode() == SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_RE
            && $product
            && (int)Mage::helper('sdm_checkout')->canCustomerGroupOverrideMinQty($customer) === 0
        ) {
            $minQty = $product->getMinQty();

            // Qty being added is smaller than min qty on product
            if ($product->getMinQty() > $request->getQty()
                && !$this->_isThereEnoughQtyInCart($product, $request->getQty(), $minQty)
            ) {
                // Adding to cart cannot be prevented, and 0 qty cannot be added.
                // So, add the min qty instead.
                $request->setQty($minQty);

                // Under the assumption that add-to-cart is Ajax and stays on product page
                $this->setMinQtyMessage(
                    "A minimum of $minQty must be ordered. Qty added increased to $minQty."
                );
            }
        }

        parent::addProduct($product, $request);

        return $this;
    }

    /**
     * Checks the cart and checks if the total qty being added plus the cart qty
     * satisfies the min. qty. requirement.
     *
     * Note: This will not cause issue with the catalog of simple and grouped
     * products. However, it will start to behave incorrectly when the same
     * simple product can be added from different types of products.
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  int                        $qtyAdded
     * @param  int                        $minQty
     * @return bool
     */
    protected function _isThereEnoughQtyInCart($product, $qtyAdded, $minQty)
    {
        $qtyInCart = 0;
        foreach ($this->getQuote()->getAllVisibleItems() as $item) {
            if ($item->getProductId() === $product->getId()) {
                $qtyInCart = $item->getQty();
                break;
            }
        }

        if ($qtyAdded + $qtyInCart >= $minQty) {
            return true;
        }

        return false;
    }
}
