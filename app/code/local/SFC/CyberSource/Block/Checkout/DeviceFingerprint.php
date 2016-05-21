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

class SFC_CyberSource_Block_Checkout_DeviceFingerprint extends Mage_Core_Block_Template
{

    const THREAT_METRIX_ORG_ID_PROD = 'k8vif92e';
    const THREAT_METRIX_ORG_ID_TEST = '1snn5n9w';

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     *
     * @return mixed
     */
    public function getConfigData($field)
    {
        $path = 'payment/' . SFC_CyberSource_Model_Method::METHOD_CODE . '/' . $field;

        return Mage::getStoreConfig($path);
    }

    public function isEnabled()
    {
        return $this->getConfigData('active') == '1' && $this->getConfigData('enable_device_fingerprint') == '1';
    }

    public function getMerchantSessionId()
    {
        /** @var Mage_Core_Helper_Data $coreHelper */
        $coreHelper = Mage::helper('core');

        if ($this->getConfigData('test') != '1') {
            // Production mode
            return $coreHelper->decrypt(trim($this->getConfigData('merchant_id'))) . Mage::getSingleton('sfc_cybersource/session')->getSessionId();
        }
        else {
            // Test mode
            return $coreHelper->decrypt(trim($this->getConfigData('test_merchant_id'))) . Mage::getSingleton('sfc_cybersource/session')->getSessionId();
        }
    }

    public function getOrgId()
    {
        if ($this->getConfigData('test') != '1') {
            // Production mode
            return self::THREAT_METRIX_ORG_ID_PROD;
        }
        else {
            // Test mode
            return self::THREAT_METRIX_ORG_ID_TEST;
        }
    }

}
