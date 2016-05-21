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
 * Helper object
 */
class SDM_Valutec_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_CONFIG_API_URL                         = 'sdm_valutec/api/url';
    const XML_PATH_CONFIG_API_CLIENT_KEY                  = 'sdm_valutec/api/client_key';
    const XML_PATH_CONFIG_API_TERMINAL_ID                 = 'sdm_valutec/api/terminal_id';
    const XML_PATH_CONFIG_API_SERVER_ID                   = 'sdm_valutec/api/server_id';
    const XML_PATH_CONFIG_API_DEBUG                       = 'sdm_valutec/api/debug';
    const XML_PATH_CONFIG_PAYMENT_DISALLOWED_METHOD       = 'payment/sdm_valutech_giftcard/disallowed_method';
    const XML_PATH_CONFIG_PAYMENT_DISALLOWED_PRODUCT_TYPE = 'payment/sdm_valutech_giftcard/disallowed_product_type';

    /**
     * Log file name
     *
     * @var string
     */
    protected $_logFile = 'sdm_valutec.log';

    /**
     * Debug logging
     *
     * @param string $message
     *
     * @return void
     */
    public function debug($message)
    {
        if (Mage::getStoreConfig(SDM_Valutec_Helper_Data::XML_PATH_CONFIG_API_DEBUG)) {
            Mage::log($message, Zend_Log::DEBUG, $this->_logFile);
        }
    }

    /**
     * Get the stored giftcard data
     *
     * @param  Varien_Object $source
     * @return array|boolean
     */
    public function getGiftcard(Varien_Object $source)
    {
        if (!$source->getSdmValutecGiftcard() && $source->getOrder()) {
            $source = $source->getOrder();
        }
        return Mage::helper('core')->jsonDecode(
            $source->getSdmValutecGiftcard()
        );
    }

    /**
     * Get methods that can't be used with giftcard
     *
     * @return string[]
     */
    public function getDisallowedMethods()
    {
        return explode(',', Mage::getStoreConfig(self::XML_PATH_CONFIG_PAYMENT_DISALLOWED_METHOD));
    }

    /**
     * Get product types that can't be used with giftcard
     *
     * @return string[]
     */
    public function getDisallowedProductTypes()
    {
        return explode(',', Mage::getStoreConfig(self::XML_PATH_CONFIG_PAYMENT_DISALLOWED_PRODUCT_TYPE));
    }
}
