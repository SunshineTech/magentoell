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
 * SDM_Shipping_Model_OnePica_AvaTax_Estimate class
 */
class SDM_Shipping_Model_OnePica_AvaTax_Estimate extends OnePica_AvaTax_Model_Avatax_Estimate
{
    /**
     * Adds shipping cost to request as item + surcharge
     *
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return integer
     */
    protected function _addShipping($address)
    {
        $lineNumber = count($this->_lines);
        $storeId = Mage::app()->getStore()->getId();
        $taxClass = Mage::helper('tax')->getShippingTaxClass($storeId);
        $surcharge = 0;
        foreach ($address->getQuote()->getAllVisibleItems() as $item) {
            $surcharge += $item->getBaseSdmShippingSurcharge() * $item->getQty();
        }
        $shippingAmount = (float) $address->getBaseShippingAmount() + $surcharge;

        $line = new Line();
        $line->setNo($lineNumber);
        $shippingSku = Mage::helper('avatax')->getShippingSku($storeId);
        $line->setItemCode($shippingSku ? $shippingSku : 'Shipping');
        $line->setDescription('Shipping costs');
        $line->setTaxCode($taxClass);
        $line->setQty(1);
        $line->setAmount($shippingAmount);
        $line->setDiscounted(false);

        $this->_lines[$lineNumber] = $line;
        $this->_request->setLines($this->_lines);
        $this->_lineToLineId[$lineNumber] = Mage::helper('avatax')->getShippingSku($storeId);
        return $lineNumber;
    }
}
