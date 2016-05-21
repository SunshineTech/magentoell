<?php
/**
 * Separation Degrees One
 *
 * Lyris Newsletter Management
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Lyris
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$this->startSetup();
$this->run("
    DROP TABLE IF EXISTS `{$this->getTable('sdm_lyris/ads')}`;
    CREATE TABLE `{$this->getTable('sdm_lyris/ads')}` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL DEFAULT '',
        `image` varchar(255) NOT NULL DEFAULT '',
        `status` smallint(6) NOT NULL DEFAULT '0',
        `created_time` datetime NULL,
        `update_time` datetime NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='Ellison Newsletter Ads';
");

$this->endSetup();
