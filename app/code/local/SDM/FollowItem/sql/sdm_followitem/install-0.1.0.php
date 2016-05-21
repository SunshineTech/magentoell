<?php
/**
 * Separation Degrees One
 *
 * Allows customers to follow an item
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_FollowItem
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$this->startSetup();

// Install main taxonomy table
$this->run("
    DROP TABLE IF EXISTS `{$this->getTable('followitem/follow')}`;
	CREATE TABLE `{$this->getTable('followitem/follow')}` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`entity_id` int(11) DEFAULT NULL,
		`store_id` smallint(5) DEFAULT NULL,
		`customer_id` int(10) DEFAULT NULL,
		`type` varchar(16) DEFAULT 'product',
		`created_at` datetime DEFAULT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->endSetup();
