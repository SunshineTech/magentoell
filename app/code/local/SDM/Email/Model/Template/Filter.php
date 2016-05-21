<?php
/**
 * Separation Degrees One
 *
 * Email template customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Email
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Core Email Template SDM_Email_Model_Template_Filter Model
 */
class SDM_Email_Model_Template_Filter extends Mage_Core_Model_Email_Template_Filter
{
    /**
     * Returns the appropriate VAT number, if applicable. Note the argument into
     * this custom directive seems unrequired.
     *
     * @return str
     */
    public function vatnumberDirective()
    {
        $construction1 = array(
            '{{var order.getShippingAddress()}}',
            'var',
            'order.getShippingAddress()'
        );
        $construction2 = array(
            '{{var order.store_id}}',
            'var',
            'order.store_id'
        );
        $shippingAddress = $this->varDirective($construction1);

        // VAT is only for UK stores
        $storeId = (int)$this->varDirective($construction2);
        $storeCode = Mage::getModel('core/store')->load($storeId)->getWebsite()->getCode();
        if ($storeCode !== SDM_Core_Helper_Data::WEBSITE_CODE_UK) {
            return '';
        }

        $countryId = $shippingAddress->getCountryId();
        $vatNum = Mage::helper('sdm_email')->getVatNumber($countryId);

        return 'VAT Number: ' . $vatNum;
    }
}
