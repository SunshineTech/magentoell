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
class SDM_Valutec_Model_Total_Creditmemo_Giftcard
    extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    /**
     * Collect gift card account totals for credit memo
     *
     * @param  Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return SDM_Valutec_Model_Total_Creditmemo_Giftcard
     */
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        if ($order->getSdmValutecGiftcardAmount()
            && $order->getSdmValutecGiftcardAmount() != $creditmemo->getSdmValutecGiftcardAmount()
        ) {
            $used     = $order->getSdmValutecGiftcardAmount();
            $baseUsed = $order->getBaseSdmValutecGiftcardAmount();
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $used);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $baseUsed);
            $creditmemo->setSdmValutecGiftcardAmount($baseUsed);
            $creditmemo->setBaseSdmValutecGiftcardAmount($used);
        }
        return $this;
    }
}
