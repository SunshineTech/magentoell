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

/**
 * This class provides error / error message mapping information
 *
 */
class SFC_CyberSource_Helper_Gateway_Error extends Mage_Core_Helper_Abstract
{

    public function mapErrorResponseToCustomerMessage(stdClass $response)
    {
        $reasonCode = (integer) $response->reasonCode;
        switch ($reasonCode) {
            case 202:
                return 'Expiration Date Mismatch - The transaction was not approved because the expiration date is expired or did not match. Please verify the expiration date is correct and try again, or try a different form of payment.';
            case 200:
            case 520:
                return 'AVS Mismatch - The transaction was not approved because the address and/or zip code did not match what the issuer has on file. Please verify the address information is correct and try again, or try a different form of payment.';
            case 209:
            case 211:
            case 230:
                return 'CVV Mismatch - The transaction was not approved because the CVV code did not match. Please verify the CVV is correct and try again, or try a different form of payment.';
            case 0:
            default:
                return 'A Gateway error occurred while processing your transaction or the transaction was not approved. Please try again or try a different form of payment.';
        }
    }

}
