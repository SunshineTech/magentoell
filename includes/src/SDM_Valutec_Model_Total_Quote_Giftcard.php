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
 * Giftcard discount object
 */
class SDM_Valutec_Model_Total_Quote_Giftcard
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * Init total model, set total code
     */
    public function __construct()
    {
        $this->setCode('sdm_valutec_giftcard');
    }

    /**
     * Collect giftcard totals for specified address
     *
     * @param  Mage_Sales_Model_Quote_Address $address
     * @return SDM_Valutec_Model_Total_Quote_Giftcard
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        $quote = $address->getQuote();
        $card = Mage::helper('core')->jsonDecode($quote->getSdmValutecGiftcard());
        if (!$card || $card['balance'] <= 0) {
            return $this;
        }
        $baseAmountToUse = min($card['balance'], $address->getBaseGrandTotal());
        $amountToUse = min($card['balance'], $address->getGrandTotal());
        $quote->setBaseSdmValutecGiftcardAmount($baseAmountToUse);
        $quote->setSdmValutecGiftcardAmount($amountToUse);
        $address->setBaseSdmValutecGiftcardAmount($baseAmountToUse);
        $address->setSdmValutecGiftcardAmount($amountToUse);
        $address->setBaseGrandTotal($address->getBaseGrandTotal() - $baseAmountToUse);
        $address->setGrandTotal($address->getGrandTotal() - $amountToUse);
        return $this;
    }

    /**
     * Return shopping cart total row items
     *
     * @param  Mage_Sales_Model_Quote_Address $address
     * @return SDM_Valutec_Model_Total_Quote_Giftcard
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $address->addTotal(array(
            'code'  => $this->getCode(),
            'title' => Mage::helper('sdm_valutec')->__('Giftcard'),
            'value' => -$address->getQuote()->getSdmValutecGiftcardAmount(),
        ));
        return $this;
    }
}
