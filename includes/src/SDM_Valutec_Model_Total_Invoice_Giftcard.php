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
class SDM_Valutec_Model_Total_Invoice_Giftcard
    extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    /**
     * Collect gift card account totals for invoice
     *
     * @param  Mage_Sales_Model_Order_Invoice $invoice
     * @return SDM_Valutec_Model_Total_Invoice_Giftcard
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();
        if ($order->getSdmValutecGiftcardAmount()
            && $order->getSdmValutecGiftcardAmount() != $invoice->getSdmValutecGiftcardAmount()
        ) {
            $used     = $order->getSdmValutecGiftcardAmount();
            $baseUsed = $order->getBaseSdmValutecGiftcardAmount();
            $invoice->setGrandTotal($invoice->getGrandTotal() - $used);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseUsed);
            $invoice->setSdmValutecGiftcardAmount($baseUsed);
            $invoice->setBaseSdmValutecGiftcardAmount($used);
        }
        return $this;
    }
}
