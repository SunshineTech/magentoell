<?php
/**
 * Separation Degrees Media
 *
 * Implements the product compatibility functionality.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Compatibility
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

$usCode = SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_US;
$ukCode = SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_UK;
$reCode = SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_RE;
$eeCode = SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_ED;

$this->startSetup();

$this->run("
    DROP TABLE IF EXISTS `{$this->getTable('compatibility/productline')}`;
    CREATE TABLE `{$this->getTable('compatibility/productline')}` (
        `productline_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Product Line ID',
        `website_ids` varchar(255) DEFAULT NULL COMMENT 'Enabled Websites',
        `name` varchar(255) DEFAULT NULL COMMENT 'Product Line Name',
        `code` varchar(255) DEFAULT NULL COMMENT 'Product Line Code',
        `type` varchar(255) DEFAULT NULL COMMENT 'Product Type',
        `image_url` varchar(255) DEFAULT NULL COMMENT 'Image Link',
        `description` text COMMENT 'Description',        PRIMARY KEY (`productline_id`),
        UNIQUE KEY `code` (`code`,`type`)
    ) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='Product Lines for Compatibility';
");

$this->run("
    DROP TABLE IF EXISTS `{$this->getTable('compatibility/compatibility')}`;
    CREATE TABLE `{$this->getTable('compatibility/compatibility')}` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Machine Line ID',
        `die_productline_id` int(10) DEFAULT NULL COMMENT 'Associated Product Line ID',
        `machine_productline_id` int(10) DEFAULT NULL COMMENT 'Catalog Product ID',
        `associated_products` varchar(255) DEFAULT NULL COMMENT 'Associated Products',
        `position` int(10) NOT NULL DEFAULT '0' COMMENT 'Position',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Compatible Machine Product Lines';
");

$this->endSetup();
