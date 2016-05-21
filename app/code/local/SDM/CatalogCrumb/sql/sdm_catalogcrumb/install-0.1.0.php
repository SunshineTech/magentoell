<?php
/**
 * Separation Degrees One
 *
 * Custom breadcrumb functionality for Ellison's catalog
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CatalogCrumb
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$this->startSetup();

$this->run("
    DROP TABLE IF EXISTS `{$this->getTable('sdm_catalogcrumb/crumb')}`;
	CREATE TABLE `{$this->getTable('sdm_catalogcrumb/crumb')}` (
		`id` int(11) NOT NULL DEFAULT '0',
		`hash` varchar(32) NOT NULL DEFAULT '',
		`filters` text,
		PRIMARY KEY (`id`),
		UNIQUE KEY `hash` (`hash`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->endSetup();
