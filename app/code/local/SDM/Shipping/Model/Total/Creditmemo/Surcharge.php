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
 * Surcharge total line item in credit memo
 */
class SDM_Shipping_Model_Total_Creditmemo_Surcharge
    extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    /**
     * Collect surcharge total in credit memo
     *
     * @param  Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return SDM_Shipping_Model_Total_Creditmemo_Surcharge
     */
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $address = $creditmemo->getOrder()->getShippingAddress() ?: $creditmemo->getOrder()->getBillingAddress();
        if ($address->getSdmShippingSurcharge()) {
            $surcharge     = $address->getSdmShippingSurcharge();
            $baseSurcharge = $address->getBaseSdmShippingSurcharge();
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $surcharge);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseSurcharge);
            $creditmemo->setSdmShippingSurcharge($surcharge);
            $creditmemo->setBaseSdmShippingSurcharge($baseSurcharge);
        }
        return $this;
    }
}
