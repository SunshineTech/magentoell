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
 * Renderer of payment form
 */
class SDM_Valutec_Block_Payment_Method_Form_Giftcard
    extends Mage_Payment_Block_Form
{
    /**
     * Set template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('sdm/valutec/payment/method/form/giftcard.phtml');
    }

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
            $this->setCard(Mage::helper('core')->jsonDecode(
                $this->getQuote()->getSdmValutecGiftcard()
            ));
        }
        return parent::getCard();
    }

    /**
     * Get the card number on the quote
     *
     * @return integer|boolean
     */
    public function getCardNumber()
    {
        if ($this->getInfoData('card_number')) {
            return $this->getInfoData('card_number');
        }
        $card = $this->getCard();
        if (!$card) {
            return false;
        }
        return $card['number'];
    }

    /**
     * Get the card pin on the quote
     *
     * @return integer|boolean
     */
    public function getCardPin()
    {
        if ($this->getInfoData('card_pin')) {
            return $this->getInfoData('card_pin');
        }
        $card = $this->getCard();
        if (!$card) {
            return false;
        }
        return $card['pin'];
    }
}
