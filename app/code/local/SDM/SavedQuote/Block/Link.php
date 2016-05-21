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
 * SDM_SavedQuote_Block_Link class
 */
class SDM_SavedQuote_Block_Link
    extends Mage_Checkout_Block_Onepage_Link
{
    /**
     * Returns the current saved quote's record ID
     *
     * @return int
     */
    public function getQuoteId()
    {
        return Mage::registry('saved_quote')->getId();
    }

    /**
     * Returns the quote review page URL only if shipping has been estimated.
     *
     * @return str
     */
    public function getReviewQuoteLink()
    {
        return $this->getUrl('savedquote/quote');
    }

    /**
     * Returns the save quote URL
     *
     * @return str
     */
    public function getPlaceQuoteLink()
    {
        return $this->getUrl('savedquote/quote/save');
    }

    /**
     * Quote to order convert URL
     *
     * @return str
     */
    public function getConvertUrl()
    {
        return Mage::getUrl('savedquote/quote/convert');
    }

    /**
     * Returns the save quote URL
     *
     * @return str
     */
    public function isCustomerLoggedIn()
    {
        return Mage::helper('savedquote')->isCustomerLoggedIn();
    }
}
