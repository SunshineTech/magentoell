<?php
/**
 * Separation Degrees One
 *
 * Ellison and Sizzix Shipping Rules
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Shipping
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Surcharge total line item in invoice
 */
class SDM_Shipping_Model_Total_Invoice_Surcharge
    extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    /**
     * Collect surcharge total in invoice
     *
     * @param  Mage_Sales_Model_Order_Invoice $invoice
     * @return SDM_Shipping_Model_Total_Invoice_Surcharge
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $address = $invoice->getShippingAddress() ?: $invoice->getBillingAddress();
        if ($address->getSdmShippingSurcharge()) {
            $surcharge     = $address->getSdmShippingSurcharge();
            $baseSurcharge = $address->getBaseSdmShippingSurcharge();
            $invoice->setGrandTotal($invoice->getGrandTotal() + $surcharge);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseSurcharge);
            $invoice->setSdmShippingSurcharge($surcharge);
            $invoice->setBaseSdmShippingSurcharge($baseSurcharge);
        }
        return $this;
    }
}
