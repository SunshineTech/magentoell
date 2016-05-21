<?php
/**
 * Separation Degrees Media
 *
 * Ellison's custom Landing Page Management System (LPMS).
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Lpms
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

$this->startSetup();

$this->run("
    DROP TABLE IF EXISTS `{$this->getTable('lpms/lpms_asset')}`;
    CREATE TABLE `{$this->getTable('lpms/lpms_asset')}` (
        `entity_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `cms_page_id` smallint(6) DEFAULT NULL,
        `name` varchar(128) DEFAULT NULL,
        `type` varchar(16) DEFAULT NULL,
        `content` text,
        `image_format` varchar(16) DEFAULT NULL,
        `sort_order` smallint(4) DEFAULT NULL,
        `start_date` datetime DEFAULT NULL,
        `end_date` datetime DEFAULT NULL,
        `is_active` tinyint(1) DEFAULT NULL,
        `week_days` varchar(24) DEFAULT NULL,
        `created_at` datetime DEFAULT NULL,
        `updated_at` datetime DEFAULT NULL,
        PRIMARY KEY (`entity_id`),
        KEY `CMS Page` (`cms_page_id`),
        CONSTRAINT `FK_LPMS_ASSET_TO_PAGE` FOREIGN KEY (`cms_page_id`)
            REFERENCES `cms_page` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->run("
    DROP TABLE IF EXISTS `{$this->getTable('lpms/lpms_asset_store')}`;
        CREATE TABLE `{$this->getTable('lpms/lpms_asset_store')}` (
        `asset_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `store_id` smallint(5) unsigned NOT NULL,
        KEY `asset_id` (`asset_id`),
        KEY `store_id` (`store_id`),
        CONSTRAINT `FK_LPMS_ASSET_STORE_TO_ASSET` FOREIGN KEY (`asset_id`)
            REFERENCES `{$this->getTable('lpms/lpms_asset')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `FK_LPMS_ASSET_STORE_TO_STORE` FOREIGN KEY (`store_id`)
            REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->run("
    DROP TABLE IF EXISTS `{$this->getTable('lpms/lpms_asset_image')}`;
    CREATE TABLE `{$this->getTable('lpms/lpms_asset_image')}` (
        `entity_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `cms_page_id` smallint(6) DEFAULT NULL,
        `cms_asset_id` int(11) unsigned DEFAULT NULL,
        `image_url` varchar(256) DEFAULT NULL,
        `image_href` varchar(256) DEFAULT NULL,
        `image_alt` varchar(512) DEFAULT NULL,
        `sort_order` smallint(4) DEFAULT NULL,
        `start_date` datetime DEFAULT NULL,
        `end_date` datetime DEFAULT NULL,
        `is_active` tinyint(1) DEFAULT NULL,
        `week_days` varchar(24) DEFAULT NULL,
        `created_at` datetime DEFAULT NULL,
        `updated_at` datetime DEFAULT NULL,
        PRIMARY KEY (`entity_id`),
        KEY `CMS Asset ID` (`cms_asset_id`),
        KEY `CMS Page` (`cms_page_id`),
        CONSTRAINT `FK_LPMS_ASSET_IMAGE_TO_ASSET` FOREIGN KEY (`cms_asset_id`)
            REFERENCES `{$this->getTable('lpms/lpms_asset')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `FK_LPMS_ASSET_IMAGE_TO_PAGE` FOREIGN KEY (`cms_page_id`)
            REFERENCES `cms_page` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->run("
    DROP TABLE IF EXISTS `{$this->getTable('lpms/lpms_asset_image_store')}`;
    CREATE TABLE `{$this->getTable('lpms/lpms_asset_image_store')}` (
        `asset_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `store_id` smallint(5) unsigned DEFAULT NULL,
        KEY `asset_id` (`asset_id`),
        KEY `store_id` (`store_id`),
        CONSTRAINT `FK_LPMS_ASSET_IMAGE_STORE_TO_STORE` FOREIGN KEY (`store_id`)
            REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `FK_LPMS_ASSET_IMAGE_STORE_TO_ASSET` FOREIGN KEY (`asset_id`)
            REFERENCES `{$this->getTable('lpms/lpms_asset_image')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->endSetup();
