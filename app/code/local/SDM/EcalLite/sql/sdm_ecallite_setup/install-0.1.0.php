<?php
/**
 * Separation Degrees One
 *
 * eCal Lite download request extension
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_EcalLite
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$this->startSetup();

$this->run("
    DROP TABLE IF EXISTS `{$this->getTable('ecallite/request')}`;
    CREATE TABLE `{$this->getTable('ecallite/request')}` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `website_id` smallint(5) unsigned NOT NULL,
        `status` varchar(255) NOT NULL DEFAULT '',
        `firstname` varchar(255) DEFAULT '',
        `lastname` varchar(255) DEFAULT NULL,
        `email` varchar(255) DEFAULT NULL,
        `code` varchar(255) DEFAULT NULL,
        `requested_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `FK_ECAL_REQUEST_STORE_ID` (`website_id`),
        CONSTRAINT `FK_ECAL_REQUEST_WEBSITE_ID` FOREIGN KEY (`website_id`) REFERENCES `core_website` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->endSetup();
