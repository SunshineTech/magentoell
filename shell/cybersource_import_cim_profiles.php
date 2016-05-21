<?php
/**
 * StoreFront Authorize.Net CIM Tokenized Payment Extension
 *
 * This source file is subject to commercial source code license of StoreFront Consulting, Inc.
 *
 * @category	SFC
 * @package    	SFC_AuthnetToken
 * @author      Garth Brantley
 * @website 	http://www.storefrontconsulting.com/
 * @copyright 	Copyright (C) 2009-2013 StoreFront Consulting, Inc. All Rights Reserved.
 * @license     http://www.storefrontconsulting.com/media/downloads/ExtensionLicense.pdf StoreFront Consulting Commercial License
 *
 */

require 'app/Mage.php';

if (!Mage::isInstalled()) {
    echo "Application is not installed yet, please complete install wizard first.";
    exit;
}

// Only for urls
// Don't remove this
$_SERVER['SCRIPT_NAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_FILENAME']);

Mage::app('admin')->setUseSessionInUrl(false);

umask(0);

try {
	// Log mem usage
	echo "\n" . 'Memory usage: ' . memory_get_usage() . "\n";

    $options = getopt('ps', array('profile', 'subscription'));
    if(array_key_exists('s', $options) || array_key_exists('subscription', $options)) {
        $fileType = 'Subscription';
    }
    else {
        $fileType = 'Profile';
    }
    $idField = $fileType . 'ID';

	echo "This will import payment profiles from CyberSource for existing Magento customers!  \n";
	$input = readline("Please enter 'YES' to confirm: ");
	if(trim($input) !== 'YES') {
		exit();
	}

    // Read in file of CyberSource payment profiles for customers
    $xml = simplexml_load_file($fileType . "Search.xml");

    // Echo some info about totals
    $profileCount = $xml->CurrencyTotals->TotalCount;
    echo "Found " . $profileCount . " payment profiles in CyberSource.\n";
    echo "Linking profiles...\n";

	// Create helper
    /** @var SFC_CyberSource_Helper_Gateway $gatewayHelper */
	$gatewayHelper = Mage::helper('sfc_cybersource/gateway');

	// Iterate list of profiles
	foreach($xml->$fileType as $curProfile) {
		// Echo
		echo "\nLooking up payment profile in gateway with id: " . $curProfile->$idField . "\n";
		// Lookup customer profile details
		$xmlPayProfile = $gatewayHelper->retrievePaymentProfile('??', (string)$curProfile->$idField);
        // Echo
        echo "Looking for Mage customer with email: " . $xmlPayProfile->email . "\n";
        // Lets check to see if a Magento customer with matching email exists
        $customer = Mage::getModel('customer/customer')->getCollection()
            ->addAttributeToFilter('email', $xmlPayProfile->email)
            ->getFirstItem();
        $customer->load($customer->getId());
        if(strlen($customer->getId())) {
            // Found a matching Magento customer
            // Echo
            echo "Found a matching Magento customer for email: " . $xmlPayProfile->email . "\n";
            // Look for existing matching payment profile for this customer in mage DB
            $payProfile = Mage::getModel('sfc_cybersource/payment_profile')->getCollection()
                ->addFieldToFilter('payment_token', (string)$curProfile->$idField)
                ->getFirstItem();
            if(strlen($payProfile->getId())) {
                // Found existing pay profile in Mage
                // Echo
                echo "Found already existing payment profile in Magento for token: " . (string)$curProfile->$idField . "\n";
            }
            else {
                // Create payment profile in Mage
                // Echo
                echo "Creating new payment profile in Magento.\n";
                $payProfile = Mage::getModel('sfc_cybersource/payment_profile');
                $payProfile->initProfileWithCustomerDefault($customer->getId());
                $payProfile->setData('payment_token', (string)$curProfile->$idField);
                $payProfile->retrieveProfileData();
                $payProfile->saveProfileData();
                $payProfile->save();
            }

        }
        else {
            // Echo
            echo "Did not find a matching Magento customer for email: " . $xmlPayProfile->email . "\n";
        }
    }

	// Log mem usage
	echo "\nMemory usage: " . memory_get_usage() . "\n";

} catch (Exception $e) {
	Mage::printException($e);
}

