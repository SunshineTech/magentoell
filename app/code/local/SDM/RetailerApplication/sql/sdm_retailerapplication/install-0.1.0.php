<?php
/**
 * Separation Degrees One
 *
 * Manages the retailer application
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_RetailerApplication
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$this->startSetup();

// Install main taxonomy table
$this->run("
    DROP TABLE IF EXISTS `{$this->getTable('retailerapplication/application')}`;
	CREATE TABLE `{$this->getTable('retailerapplication/application')}` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`customer_id` int(10) unsigned NOT NULL,
		`status` varchar(4) DEFAULT NULL,
		`application_type` varchar(24) DEFAULT NULL,
		`company_name` varchar(48) DEFAULT NULL,
		`company_website` varchar(128) DEFAULT NULL,
		`company_type` varchar(4) DEFAULT NULL,
		`company_tax_id` varchar(24) DEFAULT NULL,
		`company_years` smallint(5) DEFAULT NULL,
		`company_employees` int(8) DEFAULT NULL,
		`company_annual_sales` int(11) DEFAULT NULL,
		`company_resale_number` varchar(24) DEFAULT NULL,
		`company_authorized_buyers` text,
		`company_store_department` varchar(4) DEFAULT NULL,
		`company_store_location` varchar(4) DEFAULT NULL,
		`company_store_sqft` int(10) DEFAULT NULL,
		`brands_to_resell` varchar(64) DEFAULT NULL,
		`how_did_you_hear` varchar(24) DEFAULT NULL,
		`payment_method` varchar(4) DEFAULT NULL,
		`file_resale_tax_certificate` varchar(256) DEFAULT NULL,
		`file_business_license` varchar(256) DEFAULT NULL,
		`file_store_photo` varchar(256) DEFAULT NULL,
		`owner_address_id` int(10) unsigned NOT NULL,
		`shipping_address_id` int(10) unsigned NOT NULL,
		`billing_address_id` int(10) unsigned NOT NULL,
		`accept_application_policy` tinyint(1) DEFAULT NULL,
		`accept_terms` tinyint(1) DEFAULT NULL,
		`admin_notes` text,
		`created_at` datetime DEFAULT NULL,
		`updated_at` datetime DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `customer_id` (`customer_id`),
	CONSTRAINT `sdm_retailerapplication_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `{$this->getTable('customer/entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->endSetup();
