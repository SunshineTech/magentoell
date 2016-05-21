<?php
/**
 * Separation Degrees Media
 *
 * Ellison's custom product taxonomy implementation.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Taxonomy
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

$this->startSetup();

// Install main taxonomy table
$this->run("
    DROP TABLE IF EXISTS `{$this->getTable('taxonomy/item')}`;
    CREATE TABLE `{$this->getTable('taxonomy/item')}` (
        `entity_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(48) DEFAULT NULL,
        `code` varchar(48) DEFAULT NULL,
        `type` varchar(48) DEFAULT NULL,
        PRIMARY KEY (`entity_id`),
        UNIQUE KEY `code` (`code`,`type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->endSetup();
