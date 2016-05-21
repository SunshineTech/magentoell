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

class SFC_CyberSource_Block_Payment_Profile_Edit extends Mage_Adminhtml_Block_Template
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

    public function getIsNewProfile()
    {
        return !(strlen($this->getCimProfile()->getId()));
    }

    public function getIsSopEnabled()
    {
        return (Mage::getStoreConfig('payment/sfc_cybersource/enable_secure_acceptance') == '1');
    }

    public function getTitle()
    {
        if ($this->getIsNewProfile()) {
            // Creating a new profile
            return ' Enter New Credit Card ';
        }
        else {
            // Editing an existing profile
            return ' Edit Saved Credit Card ' . $this->getCimProfile()->getCustomerCardnumber();
        }
    }

    public function getBackUrl()
    {
        return $this->getUrl('creditcards/index/', array('_secure' => true));
    }

    public function getSaveUrl()
    {
        /** @var SFC_CyberSource_Helper_Data $helper */
        $helper = Mage::helper('sfc_cybersource');

        if ($this->getIsSopEnabled()) {
            if ($this->getIsNewProfile()) {
                // Creating a new profile
                return $helper->getSecureAcceptanceBaseUrl() . 'silent/token/create';
            }
            else {
                // Editing an existing profile
                return $helper->getSecureAcceptanceBaseUrl() . 'silent/token/update';
            }
        }
        else {
            if ($this->getIsNewProfile()) {
                // Creating a new profile
                return Mage::getUrl('creditcards/index/save', array('customerid' => $this->getRequest()->getParam('id')));
            }
            else {
                // Editing an existing profile
                return Mage::getUrl('creditcards/index/save', array('id' => $this->getCimProfile()->getId()));
            }
        }
    }

    /**
     * Retrieve payment configuration object
     *
     * @return Mage_Payment_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('payment/config');
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        $output = array();
        if (is_null($months)) {
            $months[0] = $this->__('Month');
            $months = array_merge($months, $this->_getConfig()->getMonths());
            foreach ($months as $k => $v) {
                if (strlen($k) == 1 && $k != 0) {
                    $value = '0' . $k;
                    $output[$value] = $v;
                }
                elseif ($v != 'Month') {
                    $output[$k] = $v;
                }
            }
            $this->setData('cc_months', $months);
        }

        return $output;
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (is_null($years)) {
            $years = $this->_getConfig()->getYears();
            $this->setData('cc_years', $years);
        }

        return $years;
    }

    public function getCcTypeInCyberSourceFormat()
    {
        $ccType = $this->getCimProfile()->getData('cc_type');
        if(strlen($ccType)) {
            return Mage::helper('sfc_cybersource/gateway')->mapMagentoCardTypeToCyberSource($ccType);
        }
        else {
            return '';
        }
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

    /**
     * Retrieve use verification configuration
     *
     * @return boolean
     */
    public function useVerification()
    {
        if (!$this->getIsNewProfile()) {
            return false;
        }

        $configData = $this->getConfigData('useccv');
        if (is_null($configData)) {
            return true;
        }

        return (bool)$configData;
    }

    public function getSOPAccessKey()
    {
        return $this->getConfigData('secure_acceptance_access_key_my_credit_cards');
    }

    public function getSOPSecretKey()
    {
        /** @var Mage_Core_Helper_Data $coreHelper */
        $coreHelper = Mage::helper('core');

        return $coreHelper->decrypt($this->getConfigData('secure_acceptance_secret_key_my_credit_cards'));
    }

    public function getSOPProfileId()
    {
        return $this->getConfigData('secure_acceptance_profile_id_my_credit_cards');
    }

    /**
     * Retrieve available credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        return Mage::getModel('sfc_cybersource/source_cctype')->getCcAvailableTypes();
    }

    public function getCcAvailableTypesCyberSourceFormat()
    {
        return Mage::getModel('sfc_cybersource/source_cctype')->getCcAvailableTypesCyberSourceFormat();
    }

    public function getLocaleLanguage()
    {
        return Mage::app()->getLocale()->getLocale()->getLanguage();
    }

}
