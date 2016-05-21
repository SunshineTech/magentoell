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

class SFC_CyberSource_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Constants
     */
    // Secure Acceptance URLs
    const SECURE_ACCPETANCE_PROD_URL = 'https://secureacceptance.cybersource.com/';
    const SECURE_ACCPETANCE_TEST_URL = 'https://testsecureacceptance.cybersource.com/';

    // Log file name
    const LOG_FILE = 'sfc_cybersource.log';

    /**
     * Example of how logging should be done in this extension:
     *     Mage::log($message, Zend_Log::ERR, SFC_CyberSource_Helper_Data::LOG_FILE);
     */


    /**
     * Output the current call stack to module log file
     */
    public function logCallStack()
    {
        $exception = new Exception;
        Mage::log("Current call stack:\n" . $exception->getTraceAsString(), Zend_Log::INFO, self::LOG_FILE);
    }

    public function getSecureAcceptanceBaseUrl()
    {
        $testMode = Mage::getStoreConfig('payment/' . SFC_CyberSource_Model_Method::METHOD_CODE . '/test');
        if ($testMode == '1') {
            return self::SECURE_ACCPETANCE_TEST_URL;
        }
        else {
            return self::SECURE_ACCPETANCE_PROD_URL;
        }

    }

}
