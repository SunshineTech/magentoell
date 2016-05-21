<?php
/**
 * Separation Degrees Media
 *
 * This module handles all the store locator functionality for Ellison.
 *
 * The original code from this module is based off the FME_Gmapstrlocator module. We converted their
 * module to an SDM module rather than extending from it because the amount of modifications and
 * rewrites necessary for it to fit Ellison's spec were extensive, yet we still felt there was value
 * in using FME's module as a starting point.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Gmapstrlocator
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

$installer = $this;

$installer->startSetup();
/**
 * Create tables
 */
$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('gmapstrlocator_location')};
    CREATE TABLE {$this->getTable('gmapstrlocator_location')} (
        `gmapstrlocator_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `store_name` varchar(128) NOT NULL DEFAULT '',
        `store_number` varchar(32) DEFAULT NULL,
        `store_type` varchar(32) DEFAULT NULL,
        `has_design_center` tinyint(1) DEFAULT NULL,
        `product_lines` varchar(255) DEFAULT NULL,
        `agent_type` varchar(32) DEFAULT NULL,
        `representative_serving` varchar(512) DEFAULT NULL,
        `address` varchar(255) NOT NULL,
        `address2` varchar(255) NOT NULL,
        `city` varchar(255) NOT NULL DEFAULT '',
        `state` varchar(255) DEFAULT '',
        `postal_code` varchar(255) NOT NULL,
        `country` varchar(255) NOT NULL DEFAULT '',
        `latitude` float(10,6) NOT NULL DEFAULT '0.000000',
        `longitude` float(10,6) NOT NULL DEFAULT '0.000000',
        `store_phone` varchar(32) DEFAULT '',
        `store_fax` varchar(32) DEFAULT '',
        `store_email` varchar(64) DEFAULT NULL,
        `store_website` varchar(255) DEFAULT NULL,
        `status` smallint(6) NOT NULL DEFAULT '0',
        `internal_comments` text,
        `created_time` datetime DEFAULT NULL,
        `update_time` datetime DEFAULT NULL,
    PRIMARY KEY  (`gmapstrlocator_id`)  
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    DROP TABLE IF EXISTS {$this->getTable('gmapstrlocator_store')};
    CREATE TABLE {$this->getTable('gmapstrlocator_store')} (  
        `gmapstrlocator_id` int(11) NOT NULL,                                 
        `store_id` smallint(5) unsigned NOT NULL,                             
        PRIMARY KEY  (`gmapstrlocator_id`,`store_id`),                        
        KEY `FK_GMAPSTRLOCATOR_STORE_STORE` (`store_id`)                     
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='GMapStoreLocator Stores';
");

/**
 * Set default config
 */
$prefix = "gmapstrlocator/general/";

$installer->setConfigData($prefix.'page_title', 'Store Locator');                        // Default
$installer->setConfigData($prefix.'page_title', 'Store Locator', 'websites', 1);         // Us
$installer->setConfigData($prefix.'page_title', 'Stockist List', 'websites', 3);         // Uk
$installer->setConfigData($prefix.'page_title', 'Store Locator', 'websites', 4);         // Retailer
$installer->setConfigData($prefix.'page_title', 'Distributor Locator', 'websites', 5);   // Education
$installer->setConfigData($prefix.'stores_tab', 'Stores');                               // Default
$installer->setConfigData($prefix.'stores_tab', 'Stores', 'websites', 1);                // Us
$installer->setConfigData($prefix.'stores_tab', 'Stockist List', 'websites', 3);         // Uk
$installer->setConfigData($prefix.'stores_tab', 'Store Locator', 'websites', 4);         // Retailer
$installer->setConfigData($prefix.'stores_tab', 'Distributors', 'websites', 5);          // Education

$installer->setConfigData($prefix.'map_zoom', 4);
$installer->setConfigData($prefix.'standard_lat', '39.8282');                   // Us
$installer->setConfigData($prefix.'standard_long', '-98.5795');                 // Us
$installer->setConfigData($prefix.'standard_lat', '90.98877', 'websites', 3);   // Uk
$installer->setConfigData($prefix.'standard_long', '26.12585', 'websites', 3);  // Uk

/**
 * Add CMS block
 */
$content  = "<h2>Retail Stores</h2>
<p>Search using rhe store locator above. You can also visit our retailers' websites by clicking on their logos below.</p>
<p><strong>NOTE:</strong> Not all locations carry Sizzix products; please call individual stores for information.</p>
<ul class='retailers'>
    <li><a href='#'>RETAILER GOES HERE</a></li>
    <li><a href='#'>RETAILER GOES HERE</a></li>
    <li><a href='#'>RETAILER GOES HERE</a></li>
    <li><a href='#'>RETAILER GOES HERE</a></li>
</ul>";
Mage::getModel('cms/block')
    ->setTitle('Store Locator - Retail Stores Block')
    ->setIdentifier('store_locator_retailer_stores_block')
    ->setStores(array(0))
    ->setIsActive(1)
    ->setContent($content)
    ->save();

$installer->endSetup();
