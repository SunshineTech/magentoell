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

/**
 * Create tables
 */
$this->run("
    DROP TABLE IF EXISTS {$this->getTable('gmapstrlocator_store')};
    DROP TABLE IF EXISTS {$this->getTable('gmapstrlocator_website')};
    CREATE TABLE {$this->getTable('gmapstrlocator_website')} (  
        `gmapstrlocator_id` int(11) NOT NULL,                                 
        `website_id` smallint(5) unsigned NOT NULL,                             
        PRIMARY KEY  (`gmapstrlocator_id`,`website_id`),                        
        KEY `FK_GMAPSTRLOCATOR_STORE_STORE` (`website_id`),
        CONSTRAINT `GMAPSTORELOCATOR_WEBSITE_ID_CASCADE` FOREIGN KEY (`website_id`) REFERENCES `core_store` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='GMapStoreLocator Stores';
");
