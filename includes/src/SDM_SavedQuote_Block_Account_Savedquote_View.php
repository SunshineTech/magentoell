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
 * Renderer for viewing saved quote details in account
 */
class SDM_SavedQuote_Block_Account_Savedquote_View extends Mage_Core_Block_Template
{
    /**
     * Retrieve current saved quote model
     *
     * @return SDM_SavedQuote_Model_Savedquote
     */
    public function getSavedQuote()
    {
        return Mage::registry('saved_quote');
    }

    /**
     * Return back URL
     *
     * @return str
     */
    public function getBackUrl()
    {
        return Mage::getUrl('*/*/list');
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
     * Quote checkout start URL
     *
     * @param integer $id
     *
     * @return string
     */
    public function getQuoteCheckoutStartUrl($id)
    {
        return Mage::getUrl('savedquote/quote/initCheckout', array('id' => $id));
    }

    /**
     * Return back title
     *
     * @param bool $isPreorder
     *
     * @return string
     */
    public function getBackTitle($isPreorder = false)
    {
        return $isPreorder
            ? Mage::helper('savedquote')->__('Back to Pre-Order')
            : Mage::helper('savedquote')->__('Back to Saved Quotes');
    }

    /**
     * Returns some information about this quote.
     *
     * @see SDM_SavedQuote_Helper_Data::canBePurchased()
     *
     * @return str
     */
    public function aboutThisQuote()
    {
        $msg = '';
        $flag = (int)$this->getSavedQuote()->getIsActive();

        if (!Mage::helper('savedquote')->checkExpiration($this->getSavedQuote())) {
            return 'Order cannot be converted because the quote is expired';
        }

        if ($flag === (int)SDM_SavedQuote_Helper_Data::INACTIVE_FLAG) {
            return 'This quote has already been converted.';

            // Only case where conversion is possible
        } elseif ($flag === (int)SDM_SavedQuote_Helper_Data::ACTIVE_FLAG) {
            foreach ($this->getSavedQuote()->getItemCollection() as $item) {
                if (!$item->canBePurchased()) {
                    $msg = 'Some of the items in your quote are either out of stock or '
                        . 'unavailable at this time. Please Contact Customer Service '
                        . 'to convert this quote to an order.';

                    if ($this->getSavedQuote()->getDiscount() > 0) {
                        $msg .= ' Discounts are already applied to each item.';
                        return $msg;
                    }
                }
            }

            if (!empty($msg)) {
                return $msg;
            }

            $msg = 'This quote can be converted into an order.';
        } else {
            return 'This quote has been canceled';
        }

        if ($this->getSavedQuote()->getDiscount() > 0) {
            $msg .= ' Discounts are already applied to each item.';
        }

        return $msg;
    }

    /**
     * Get preorder details
     *
     * @return string
     */
    public function aboutThisPreOrder()
    {
        switch ((int) $this->getSavedQuote()->getIsActive()) {
            case SDM_SavedQuote_Helper_Data::PREORDER_PENDING_FLAG:
                return $this->__('This Pre-order is pending stock availability. '
                    . ' You will receive an email once your Pre-order is ready to be completed.');
            case SDM_SavedQuote_Helper_Data::PREORDER_APPROVED_FLAG:
                return $this->__('Available. Please complete Pre-order.');
            case SDM_SavedQuote_Helper_Data::PREORDER_DENIED_FLAG:
                return $this->__('This pre-order request has been cancelled.');
        }
        return $this->__('There was a problem with this pre-order.  Please contact support.');
    }
}
