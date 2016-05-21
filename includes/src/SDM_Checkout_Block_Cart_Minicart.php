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
 * SDM_Checkout_Block_Cart_Sidebar class
 */
class SDM_Checkout_Block_Cart_Minicart extends Mage_Checkout_Block_Cart_Minicart
{
    /**
     * Get shopping cart subtotal.
     *
     * It will include tax, if required by config settings.
     *
     * @return decimal
     */
    public function getSubtotal()
    {
        $quote = $this->helper('checkout/cart')->getQuote();
        $totals = $quote->getTotals();

        // Get subtotal, preferably from totals array
        $subtotal = $quote->getSubtotal();
        $subtotal = (float)($totals['subtotal'] ? $totals['subtotal']->getValue() : $subtotal);

        // Return it, formatted
        return $this->helper('core')->formatPrice($subtotal, false);
    }
}
