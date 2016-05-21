<?php
/**
 * Separation Degrees One
 *
 * Banner Ads
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Banner
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */
$this->startSetup();
$this->run("
    DROP TABLE IF EXISTS `{$this->getTable('slider/slider')}`;
    CREATE TABLE `{$this->getTable('slider/slider')}` (
        `slider_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL DEFAULT '',
        `sliderimage` varchar(255) NOT NULL DEFAULT '',
        `bannerurl` varchar(255) NOT NULL DEFAULT '',
        `status` smallint(6) NOT NULL DEFAULT '0',
        `created_time` datetime NULL,
        `update_time` datetime NULL,
        PRIMARY KEY (`slider_id`)
    ) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='Ellison Banner Ads';
");

$this->endSetup();
