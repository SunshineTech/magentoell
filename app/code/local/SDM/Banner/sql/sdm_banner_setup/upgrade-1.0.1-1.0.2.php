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
    DROP TABLE IF EXISTS `{$this->getTable('slider/stores')}`;
    CREATE TABLE IF NOT EXISTS `{$this->getTable('slider/stores')}` (
        `store_id` int(11) unsigned NOT NULL auto_increment,
        `slider_id` int(11) unsigned NOT NULL,
        PRIMARY KEY (`store_id`,`slider_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ellison Banner Ads';

    DROP TABLE IF EXISTS `{$this->getTable('slider/pages')}`;
    CREATE TABLE IF NOT EXISTS `{$this->getTable('slider/pages')}` (
        `page_id` int(11) unsigned NOT NULL auto_increment,
        `slider_id` int(11) unsigned NOT NULL,
        `layout_id` int(11) unsigned NOT NULL,
        PRIMARY KEY (`page_id`, `slider_id`),
        KEY `FK_SDM_BANNER_PAGE_TO_LAYOUT` (`layout_id`),
        CONSTRAINT `FK_SDM_BANNER_PAGE_TO_LAYOUT` FOREIGN KEY (`layout_id`)
        REFERENCES `{$this->getTable('slider/layouts')}` (`layout_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
        ) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='Ellison Banner Ads';

    DROP TABLE IF EXISTS `{$this->getTable('slider/layouts')}`;
    CREATE TABLE IF NOT EXISTS `{$this->getTable('slider/layouts')}` (
        `layout_id` int(11) unsigned NOT NULL auto_increment,
        `title` varchar(255) NOT NULL DEFAULT '',
        `layout_update_xml` text,
        `layout_handle` text,
        PRIMARY KEY (`layout_id`)
        ) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='Ellison Banner Ads';

    INSERT INTO `{$this->getTable('slider/layouts')}` (`layout_id`, `title`, `layout_update_xml`, `layout_handle`)
        VALUES
        (1, 'Home Page', '<reference name=\"head\">\r\n<action method=\"addItem\">\r\n<type>skin_js</type>\r\n<name>js/sdm/banner.js</name>\r\n</action>\r\n</reference>\r\n<reference name=\"content\">\r\n<block type=\"slider/slider\" name=\"slider\" as=\"slider\" before=\"-\" template=\"sdm/banner/slider.phtml\" />\r\n</reference>', 'cms_index_index'),
        (2, 'Category Page', '<reference name=\"head\">\r\n<action method=\"addItem\">\r\n<type>skin_js</type>\r\n<name>js/sdm/banner.js</name>\r\n</action>\r\n</reference>\r\n<reference name=\"content\">\r\n<block type=\"slider/slider\" name=\"slider\" as=\"slider\" before=\"-\" template=\"sdm/banner/slider.phtml\" />\r\n</reference>', 'catalog_category_view'),
        (3, 'Product Page', '<reference name=\"head\">\r\n<action method=\"addItem\">\r\n<type>skin_js</type>\r\n<name>js/sdm/banner.js</name>\r\n</action>\r\n</reference>\r\n<reference name=\"content\">\r\n<block type=\"slider/slider\" name=\"slider\" as=\"slider\" before=\"-\" template=\"sdm/banner/slider.phtml\" />\r\n</reference>', 'catalog_product_view'),
        (4, 'Calendar Page', '<reference name=\"head\">\r\n<action method=\"addItem\">\r\n<type>skin_js</type>\r\n<name>js/sdm/banner.js</name>\r\n</action>\r\n</reference>\r\n<reference name=\"content\">\r\n<block type=\"slider/slider\" name=\"slider\" as=\"slider\" before=\"-\" template=\"sdm/banner/slider.phtml\" />\r\n</reference>', 'calendar_index_index'),
        (5, 'Shopping Cart Page', '<reference name=\"head\">\r\n<action method=\"addItem\">\r\n<type>skin_js</type>\r\n<name>js/sdm/banner.js</name>\r\n</action>\r\n</reference>\r\n<reference name=\"content\">\r\n<block type=\"slider/slider\" name=\"slider\" as=\"slider\" before=\"-\" template=\"sdm/banner/slider.phtml\" />\r\n</reference>', 'checkout_cart_index');

");

$this->endSetup();
