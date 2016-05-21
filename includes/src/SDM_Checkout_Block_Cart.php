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
 * Cart display class
 */
class SDM_Checkout_Block_Cart extends Mage_Checkout_Block_Cart
{
    /**
     * "Keep shopping" URL points to the catalog category page, if it's not
     * already set in session.
     *
     * @return str
     */
    public function getContinueShoppingUrl()
    {
        $url = $this->getData('continue_shopping_url');
        if (is_null($url)) {
            $url = Mage::getSingleton('checkout/session')->getContinueShoppingUrl(true);
            if (!$url) {
                $url = Mage::getUrl() . 'catalog';  // Hard-coded "Catalog" category URL key
            }
            $this->setData('continue_shopping_url', $url);
        }
        return $url;
    }
}
