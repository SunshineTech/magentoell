<?php
/**
 * Separation Degrees One
 *
 * Valutec Giftcard Integration
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Valutec
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Total renderer
 */
class SDM_Valutec_Block_Checkout_Cart_Total
    extends Mage_Checkout_Block_Total_Default
{
    /**
     * Total template
     *
     * @var string
     */
    protected $_template = 'sdm/valutec/giftcard/cart/total.phtml';

    /**
     * The customer's quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * The card applied to the quote
     *
     * @return array|boolean
     */
    public function getCard()
    {
        if (!$this->hasCard()) {
            $this->setCard(
                $this->helper('sdm_valutec')->getGiftcard($this->getQuote())
            );
        }
        return parent::getCard();
    }

    /**
     * The card applied to the quote
     *
     * @return float
     */
    public function getAmount()
    {
        if (!$this->getCard()) {
            return 0;
        }
        return $this->getQuote()->getSdmValutecGiftcardAmount();
    }
}
