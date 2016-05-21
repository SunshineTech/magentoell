<?php
/**
 * Separation Degrees One
 *
 * eClips Software Download
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Eclips
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$this->startSetup();

$this->run("
    DROP TABLE IF EXISTS `{$this->getTable('eclips/request')}`;
    CREATE TABLE `{$this->getTable('eclips/request')}` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `count` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$this->endSetup();
