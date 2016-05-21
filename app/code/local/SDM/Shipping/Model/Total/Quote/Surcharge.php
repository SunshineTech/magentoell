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
 * Surcharge total line item in quote
 */
class SDM_Shipping_Model_Total_Quote_Surcharge
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * Init total model, set total code
     */
    public function __construct()
    {
        $this->setCode('sdm_shipping_surcharge');
    }

    /**
     * Collect any applicable totals and apply
     *
     * @param  Mage_Sales_Model_Quote_Address $address
     * @return SDM_Shipping_Model_Total_Quote_Surcharge
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        if ($address->getAddressType() == Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING) {
            $surchargeAmount = Mage::helper('sdm_shipping')->getSurchargeAmount($address->getQuote());
            $address->setBaseSdmShippingSurcharge($surchargeAmount);
            $address->setSdmShippingSurcharge($surchargeAmount);
            $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseSdmShippingSurcharge());
            $address->setGrandTotal($address->getGrandTotal() + $address->getSdmShippingSurcharge());
        }
        return $this;
    }

    /**
     * Return shopping cart total row items
     *
     * @param  Mage_Sales_Model_Quote_Address $address
     * @return SDM_Shipping_Model_Total_Quote_Surcharge
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if ($address->getSdmShippingSurcharge()) {
            $address->addTotal(array(
                'code'  => $this->getCode(),
                'title' => Mage::helper('sdm_shipping')->__('Shipping &amp; Handling Surcharge'),
                'value' => $address->getSdmShippingSurcharge(),
            ));
        }
        return $this;
    }
}
