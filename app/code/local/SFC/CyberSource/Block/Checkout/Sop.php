<?php
/**
 * StoreFront CyberSource Tokenized Payment Extension for Magento
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of StoreFront Consulting, Inc.
 *
 * @category  SFC
 * @package   SFC_CyberSource
 * @author    Garth Brantley <garth@storefrontconsulting.com>
 * @copyright 2009-2013 StoreFront Consulting, Inc. All Rights Reserved.
 * @license   http://www.storefrontconsulting.com/media/downloads/ExtensionLicense.pdf StoreFront Consulting Commercial License
 * @link      http://www.storefrontconsulting.com/cybersource-saved-credit-cards-extension-for-magento/
 *
 */

class SFC_CyberSource_Block_Checkout_Sop extends Mage_Checkout_Block_Onepage_Abstract
{

    public function getSopCreatePostUrl()
    {
        /** @var SFC_CyberSource_Helper_Data $helper */
        $helper = Mage::helper('sfc_cybersource');

        return $helper->getSecureAcceptanceBaseUrl() . 'silent/token/create';
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|Mage_Core_Model_Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = Mage::app()->getStore();
        }
        $path = 'payment/' . SFC_CyberSource_Model_Method::METHOD_CODE . '/' . $field;

        return Mage::getStoreConfig($path, $storeId);
    }

    public function getSOPAccessKey()
    {
        return $this->getConfigData('secure_acceptance_access_key_checkout');
    }

    public function getSOPSecretKey()
    {
        /** @var Mage_Core_Helper_Data $coreHelper */
        $coreHelper = Mage::helper('core');

        return $coreHelper->decrypt($this->getConfigData('secure_acceptance_secret_key_checkout'));
    }

    public function getSOPProfileId()
    {
        return $this->getConfigData('secure_acceptance_profile_id_checkout');
    }

    public function getBillingAddress()
    {
        return $this->getQuote()->getBillingAddress();
    }

    public function getCyberSourceCardType()
    {
        /** @var SFC_CyberSource_Helper_Gateway $gatewayHelper */
        $gatewayHelper = Mage::helper('sfc_cybersource/gateway');
        // Get cc type from quote payment
        $mageCardType = $this->getQuote()->getPayment()->getCcType();
        // Convert Mage cc type to CyberSource format
        $cybersourceCardType = $gatewayHelper->mapMagentoCardTypeToCyberSource($mageCardType);

        // Return CyberSource format card type
        return $cybersourceCardType;
    }

    public function getCyberSourceExpirationDate()
    {
        // Get date from quote->payment
        $expMonth = $this->getQuote()->getPayment()->getCcExpMonth();
        $expYear = $this->getQuote()->getPayment()->getCcExpYear();
        // Format for CyberSource
        $cyberSourceExpDate = sprintf("%02s", $expMonth) . '-' . $expYear;

        // Return formatted
        return $cyberSourceExpDate;
    }

    public function getErrorMessageText()
    {
        return $this->getConfigData('general_error_message_text');
    }

    public function getLocaleLanguage()
    {
        return Mage::app()->getLocale()->getLocale()->getLanguage();
    }

}
