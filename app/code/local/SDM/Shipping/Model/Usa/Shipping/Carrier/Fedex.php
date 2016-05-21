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
 * Customize fedex shipping rates
 */
class SDM_Shipping_Model_Usa_Shipping_Carrier_Fedex
    extends Mage_Usa_Model_Shipping_Carrier_Fedex
{
    /**
     * Collect and get rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return Mage_Shipping_Model_Rate_Result|bool|null
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $isNoncontinental = in_array(
            $request->getDestRegionCode(),
            explode(',', Mage::getStoreConfig('carriers/sdm_shipping_table_us_noncontinental/state'))
        );
        $isMilitary = in_array(
            $request->getDestRegionCode(),
            explode(',', Mage::getStoreConfig('carriers/sdm_shipping_table_us_noncontinental/state_military'))
        );
        // Prevent fedex from collection rates if we are going to use our table rates instead
        if ($request->getDestCountryId() == SDM_Shipping_Model_Carrier_Table_Us::COUNTRY_ID_US
            && ($isNoncontinental || $isMilitary)
        ) {
            return false;
        }
        return parent::collectRates($request);
    }
}
