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
 * SDM_SavedQuote_Block_Savedquote class
 */
class SDM_SavedQuote_Block_Savedquote extends Mage_Checkout_Block_Cart
{
    /**
     * Returns the saved quote saved in cache
     *
     * To see which process saves it into session, see below.
     *
     * @see SDM_SavedQuote_QuoteController::indexAction()
     *
     * @return SDM_SavedQuote_Model_SavedQuote
     */
    public function getPendingSavedQuote()
    {
        return Mage::registry('saved_quote');
    }

    /**
     * Get this quote id
     *
     * @return integer
     */
    public function getSavedQuoteId()
    {
        return $this->getPendingSavedQuote()->getId();
    }

    /**
     * Get this quote name
     *
     * @return string
     */
    public function getSavedQuoteName()
    {
        return $this->getPendingSavedQuote()->getName();
    }

    /**
     * Get all saved quote items
     *
     * @return SDM_SavedQuote_Model_Resource_Savedquote_Item_Collection
     */
    public function getItems()
    {
        return $this->getPendingSavedQuote()->getItemCollection();
    }

    /**
     * Get item row html
     *
     * @param SDM_SavedQuote_Model_Resource_Savedquote_Item $item
     *
     * @return string
     */
    public function getSavedQuoteItemHtml(SDM_SavedQuote_Model_Savedquote_Item $item)
    {
        // return;
        $renderer = $this->getItemRenderer($item->getProductType())->setSavedQuoteItem($item);
        return $renderer->toHtml();
    }

    /**
     * Returns the customer
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return Mage::helper('savedquote')->getCustomer();
    }

    /**
     * Checks if customer has saved addresses
     *
     * @return bool
     */
    public function customerHasAddresses()
    {
        return Mage::helper('customer')->customerHasAddresses();
    }

    /**
     * Returns the shoppiong cart URL
     *
     * @return string
     */
    public function getShoppingCartUrl()
    {
        return Mage::getUrl('checkout/cart');
    }

    /**
     * Get the specified helper
     *
     * @param string $name
     *
     * @return SDM_SavedQuote_Helper_Data
     */
    protected function _getHelper($name = '')
    {
        if (empty($name)) {
            $name = 'savedquote';
        } else {
            $name = "savedquote/$name";
        }

        return Mage::helper($name);
    }
}
