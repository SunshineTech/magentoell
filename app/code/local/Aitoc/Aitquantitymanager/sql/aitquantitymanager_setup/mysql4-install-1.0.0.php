<?php
/**
 * Multi-Location Inventory
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitquantitymanager
 * @version      2.2.3
 * @license:     rJhV4acfvLy4sPgpe7MoLJnfOEhDVfWVuKRvbpcv30
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
if (version_compare( Mage::getVersion(), '1.4.0.0', 'ge') && version_compare( Mage::getVersion(), '1.4.1.0', 'lt'))
{
    $installer = $this;
    /* @var $installer Aitoc_Aitdownloadablefiles_Model_Resource_Eav_Mysql4_Setup */

    $installer->startSetup();

    $conn = $installer->getConnection();
    /* @var $conn Varien_Db_Adapter_Pdo_Mysql */


    $installer->startSetup();


    $installer->run("
    CREATE TABLE IF NOT EXISTS `{$installer->getTable('aitquantitymanager/stock_settings')}` (
      `code` varchar(255) NOT NULL default '',
      `value` int(10) unsigned NOT NULL default '0'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    $installer->run("
    CREATE TABLE IF NOT EXISTS `{$installer->getTable('aitquantitymanager/stock_item')}` (
      `item_id` int(10) unsigned NOT NULL auto_increment,
      `website_id` int(10) unsigned NOT NULL default '0',
      `product_id` int(10) unsigned NOT NULL default '0',
      `stock_id` smallint(4) unsigned NOT NULL default '0',
      `qty` decimal(12,4) NOT NULL default '0.0000',
      `min_qty` decimal(12,4) NOT NULL default '0.0000',
      `use_config_min_qty` tinyint(1) unsigned NOT NULL default '1',
      `is_qty_decimal` tinyint(1) unsigned NOT NULL default '0',
      `backorders` tinyint(3) unsigned NOT NULL default '0',
      `use_config_backorders` tinyint(1) unsigned NOT NULL default '1',
      `min_sale_qty` decimal(12,4) NOT NULL default '1.0000',
      `use_config_min_sale_qty` tinyint(1) unsigned NOT NULL default '1',
      `max_sale_qty` decimal(12,4) NOT NULL default '0.0000',
      `use_config_max_sale_qty` tinyint(1) unsigned NOT NULL default '1',
      `is_in_stock` tinyint(1) unsigned NOT NULL default '0',
      `low_stock_date` datetime default NULL,
      `notify_stock_qty` decimal(12,4) default NULL,
      `use_config_notify_stock_qty` tinyint(1) unsigned NOT NULL default '1',
      `manage_stock` tinyint(1) unsigned NOT NULL default '0',
      `use_config_manage_stock` tinyint(1) unsigned NOT NULL default '1',
      `stock_status_changed_automatically` tinyint(1) unsigned NOT NULL default '0',
      `use_default_website_stock` tinyint(1) unsigned NOT NULL default '1',
      PRIMARY KEY  (`item_id`),
      UNIQUE KEY `IDX_AITOC_STOCK_PRODUCT_WEBSITE` (`product_id`,`stock_id`,`website_id`),
      KEY `FK_AITQUANTITYMANAGER_STOCK_ITEM_PRODUCT` (`product_id`),
      KEY `FK_AITQUANTITYMANAGER_STOCK_ITEM_STOCK` (`stock_id`),
      CONSTRAINT `FK_AITQUANTITYMANAGER_STOCK_ITEM_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT `FK_AITQUANTITYMANAGER_STOCK_ITEM_STOCK` FOREIGN KEY (`stock_id`) REFERENCES `{$this->getTable('cataloginventory_stock')}` (`stock_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Inventory Stock Item Data' AUTO_INCREMENT=1 ;
    ");
    /*
    $conn->addConstraint(
        'FK_AITQUANTITYMANAGER_STOCK_ITEM_PRODUCT', $installer->getTable('aitquantitymanager/stock_item'), 'product_id', $installer->getTable('catalog/product'), 'entity_id'
    );

    $conn->addConstraint(
        'FK_AITQUANTITYMANAGER_STOCK_ITEM_STOCK', $installer->getTable('aitquantitymanager/stock_item'), 'stock_id', $installer->getTable('cataloginventory/stock'), 'stock_id'
    );
    */
    $installer->run("
    CREATE TABLE IF NOT EXISTS `{$installer->getTable('aitquantitymanager/stock_status')}` (
      `product_id` int(10) unsigned NOT NULL,
      `website_id` smallint(5) unsigned NOT NULL default '0',
      `stock_id` smallint(4) unsigned NOT NULL,
      `qty` decimal(12,4) NOT NULL default '0.0000',
      `stock_status` tinyint(3) unsigned NOT NULL,
      PRIMARY KEY  (`product_id`,`website_id`,`stock_id`),
      CONSTRAINT `FK_AITQUANTITYMANAGER_STOCK_STATUS_STOCK` FOREIGN KEY (`stock_id`) REFERENCES `{$installer->getTable('cataloginventory_stock')}` (`stock_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT `FK_AITQUANTITYMANAGER_STOCK_STATUS_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    //   CONSTRAINT `FK_AITQUANTITYMANAGER_STOCK_STATUS_WEBSITE` FOREIGN KEY (`website_id`) REFERENCES `{$installer->getTable('core_website')}` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE


    /*
    $conn->addConstraint(
        'FK_AITQUANTITYMANAGER_STOCK_STATUS_PRODUCT', $installer->getTable('aitquantitymanager/stock_status'), 'product_id', $installer->getTable('catalog/product'), 'entity_id'
    );

    $conn->addConstraint(
        'FK_AITQUANTITYMANAGER_STOCK_STATUS_STOCK', $installer->getTable('aitquantitymanager/stock_status'), 'stock_id', $installer->getTable('cataloginventory/stock'), 'stock_id'
    );
    */
    // create default website
    /*
    $installer->run("
    INSERT INTO `{$installer->getTable('core/website')}` (
    `website_id` ,
    `code` ,
    `name` ,
    `sort_order` ,
    `default_group_id` ,
    `is_default`
    )
    VALUES (
    NULL , 'aitoccode', '', '0', '0', '0'
    );");
    */
    $installer->endSetup();
}
elseif (version_compare(Mage::getVersion(), '1.4.1.0', 'ge') && version_compare(Mage::getVersion(), '1.6.0.0', 'lt'))
{
    $installer = $this;
    /* @var $installer Aitoc_Aitdownloadablefiles_Model_Resource_Eav_Mysql4_Setup */

    $installer->startSetup();

    $conn = $installer->getConnection();
    /* @var $conn Varien_Db_Adapter_Pdo_Mysql */


    $installer->startSetup();


    $installer->run("
    CREATE TABLE IF NOT EXISTS `{$installer->getTable('aitquantitymanager/stock_settings')}` (
      `code` varchar(255) NOT NULL default '',
      `value` int(10) unsigned NOT NULL default '0'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    $installer->run("
    CREATE TABLE IF NOT EXISTS `{$installer->getTable('aitquantitymanager/stock_item')}` (
      `item_id` int(10) unsigned NOT NULL auto_increment,
      `website_id` int(10) unsigned NOT NULL default '0',
      `product_id` int(10) unsigned NOT NULL default '0',
      `stock_id` smallint(4) unsigned NOT NULL default '0',
      `qty` decimal(12,4) NOT NULL default '0.0000',
      `min_qty` decimal(12,4) NOT NULL default '0.0000',
      `use_config_min_qty` tinyint(1) unsigned NOT NULL default '1',
      `is_qty_decimal` tinyint(1) unsigned NOT NULL default '0',
      `backorders` tinyint(3) unsigned NOT NULL default '0',
      `use_config_backorders` tinyint(1) unsigned NOT NULL default '1',
      `min_sale_qty` decimal(12,4) NOT NULL default '1.0000',
      `use_config_min_sale_qty` tinyint(1) unsigned NOT NULL default '1',
      `max_sale_qty` decimal(12,4) NOT NULL default '0.0000',
      `use_config_max_sale_qty` tinyint(1) unsigned NOT NULL default '1',
      `is_in_stock` tinyint(1) unsigned NOT NULL default '0',
      `low_stock_date` datetime default NULL,
      `notify_stock_qty` decimal(12,4) default NULL,
      `use_config_notify_stock_qty` tinyint(1) unsigned NOT NULL default '1',
      `manage_stock` tinyint(1) unsigned NOT NULL default '0',
      `use_config_manage_stock` tinyint(1) unsigned NOT NULL default '1',
      `stock_status_changed_automatically` tinyint(1) unsigned NOT NULL default '0',
      `use_default_website_stock` tinyint(1) unsigned NOT NULL default '1',

      `use_config_qty_increments` tinyint(1) unsigned NOT NULL default '1',
      `qty_increments` decimal(12,4) NOT NULL default '0.0000',
      `use_config_enable_qty_increments` tinyint(1) unsigned NOT NULL default '1',
      `enable_qty_increments` tinyint(1) unsigned NOT NULL default '0',

      PRIMARY KEY  (`item_id`),
      UNIQUE KEY `IDX_AITOC_STOCK_PRODUCT_WEBSITE` (`product_id`,`stock_id`,`website_id`),
      KEY `FK_AITQUANTITYMANAGER_STOCK_ITEM_PRODUCT` (`product_id`),
      KEY `FK_AITQUANTITYMANAGER_STOCK_ITEM_STOCK` (`stock_id`),
      CONSTRAINT `FK_AITQUANTITYMANAGER_STOCK_ITEM_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT `FK_AITQUANTITYMANAGER_STOCK_ITEM_STOCK` FOREIGN KEY (`stock_id`) REFERENCES `{$this->getTable('cataloginventory_stock')}` (`stock_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Inventory Stock Item Data' AUTO_INCREMENT=1 ;
    ");
    /*
    $conn->addConstraint(
        'FK_AITQUANTITYMANAGER_STOCK_ITEM_PRODUCT', $installer->getTable('aitquantitymanager/stock_item'), 'product_id', $installer->getTable('catalog/product'), 'entity_id'
    );

    $conn->addConstraint(
        'FK_AITQUANTITYMANAGER_STOCK_ITEM_STOCK', $installer->getTable('aitquantitymanager/stock_item'), 'stock_id', $installer->getTable('cataloginventory/stock'), 'stock_id'
    );
    */
    $installer->run("
    CREATE TABLE IF NOT EXISTS `{$installer->getTable('aitquantitymanager/stock_status')}` (
      `product_id` int(10) unsigned NOT NULL,
      `website_id` smallint(5) unsigned NOT NULL default '0',
      `stock_id` smallint(4) unsigned NOT NULL,
      `qty` decimal(12,4) NOT NULL default '0.0000',
      `stock_status` tinyint(3) unsigned NOT NULL,
      PRIMARY KEY  (`product_id`,`website_id`,`stock_id`),
      CONSTRAINT `FK_AITQUANTITYMANAGER_STOCK_STATUS_STOCK` FOREIGN KEY (`stock_id`) REFERENCES `{$installer->getTable('cataloginventory_stock')}` (`stock_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT `FK_AITQUANTITYMANAGER_STOCK_STATUS_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    //   CONSTRAINT `FK_AITQUANTITYMANAGER_STOCK_STATUS_WEBSITE` FOREIGN KEY (`website_id`) REFERENCES `{$installer->getTable('core_website')}` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE


    /*
    $conn->addConstraint(
        'FK_AITQUANTITYMANAGER_STOCK_STATUS_PRODUCT', $installer->getTable('aitquantitymanager/stock_status'), 'product_id', $installer->getTable('catalog/product'), 'entity_id'
    );

    $conn->addConstraint(
        'FK_AITQUANTITYMANAGER_STOCK_STATUS_STOCK', $installer->getTable('aitquantitymanager/stock_status'), 'stock_id', $installer->getTable('cataloginventory/stock'), 'stock_id'
    );
    */
    // create default website
    /*
    $installer->run("
    INSERT INTO `{$installer->getTable('core/website')}` (
    `website_id` ,
    `code` ,
    `name` ,
    `sort_order` ,
    `default_group_id` ,
    `is_default`
    )
    VALUES (
    NULL , 'aitoccode', '', '0', '0', '0'
    );");
    */
    $installer->endSetup();
}
elseif (version_compare(Mage::getVersion(), '1.6.0.0', 'ge'))
{
	$installer = $this;
    /* @var $installer Aitoc_Aitdownloadablefiles_Model_Resource_Eav_Mysql4_Setup */

    $installer->startSetup();

    $conn = $installer->getConnection();
    /* @var $conn Varien_Db_Adapter_Pdo_Mysql */


    $installer->startSetup();


    $installer->run("
    CREATE TABLE IF NOT EXISTS `{$installer->getTable('aitquantitymanager/stock_settings')}` (
      `code` varchar(255) NOT NULL default '',
      `value` int(10) unsigned NOT NULL default '0'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    $installer->run("   
    CREATE TABLE IF NOT EXISTS `{$installer->getTable('aitquantitymanager/stock_item')}` (
	  `item_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `website_id` int(10) unsigned NOT NULL DEFAULT '0',
	  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
	  `stock_id` smallint(4) unsigned NOT NULL DEFAULT '0',
	  `qty` decimal(12,4) NOT NULL DEFAULT '0.0000',
	  `min_qty` decimal(12,4) NOT NULL DEFAULT '0.0000',
	  `use_config_min_qty` tinyint(1) unsigned NOT NULL DEFAULT '1',
	  `is_qty_decimal` tinyint(1) unsigned NOT NULL DEFAULT '0',
	  `backorders` tinyint(3) unsigned NOT NULL DEFAULT '0',
	  `use_config_backorders` tinyint(1) unsigned NOT NULL DEFAULT '1',
	  `min_sale_qty` decimal(12,4) NOT NULL DEFAULT '1.0000',
	  `use_config_min_sale_qty` tinyint(1) unsigned NOT NULL DEFAULT '1',
	  `max_sale_qty` decimal(12,4) NOT NULL DEFAULT '0.0000',
	  `use_config_max_sale_qty` tinyint(1) unsigned NOT NULL DEFAULT '1',
	  `is_in_stock` tinyint(1) unsigned NOT NULL DEFAULT '0',
	  `low_stock_date` datetime DEFAULT NULL,
	  `notify_stock_qty` decimal(12,4) DEFAULT NULL,
	  `use_config_notify_stock_qty` tinyint(1) unsigned NOT NULL DEFAULT '1',
	  `manage_stock` tinyint(1) unsigned NOT NULL DEFAULT '0',
	  `use_config_manage_stock` tinyint(1) unsigned NOT NULL DEFAULT '1',
	  `stock_status_changed_automatically` tinyint(1) unsigned NOT NULL DEFAULT '0',
	  `use_default_website_stock` tinyint(1) unsigned NOT NULL DEFAULT '1',
	  `use_config_qty_increments` tinyint(1) unsigned NOT NULL DEFAULT '1',
	  `qty_increments` decimal(12,4) NOT NULL DEFAULT '0.0000',
	  `use_config_enable_qty_increments` tinyint(1) unsigned NOT NULL DEFAULT '1',
	  `enable_qty_increments` tinyint(1) unsigned NOT NULL DEFAULT '0',
	  PRIMARY KEY (`item_id`),
	  UNIQUE KEY `IDX_AITOC_STOCK_PRODUCT_WEBSITE` (`product_id`,`stock_id`,`website_id`),
	  KEY `FK_AITQUANTITYMANAGER_STOCK_ITEM_PRODUCT` (`product_id`),
	  KEY `FK_AITQUANTITYMANAGER_STOCK_ITEM_STOCK` (`stock_id`),
	  CONSTRAINT `FK_AITQUANTITYMANAGER_STOCK_ITEM_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT `FK_AITQUANTITYMANAGER_STOCK_ITEM_STOCK` FOREIGN KEY (`stock_id`) REFERENCES `{$this->getTable('cataloginventory_stock')}` (`stock_id`) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Inventory Stock Item Data' AUTO_INCREMENT=1 ;
    ");

    $installer->run("
    CREATE TABLE IF NOT EXISTS `{$installer->getTable('aitquantitymanager/stock_status')}` (
      `product_id` int(10) unsigned NOT NULL,
      `website_id` smallint(5) unsigned NOT NULL default '0',
      `stock_id` smallint(4) unsigned NOT NULL,
      `qty` decimal(12,4) NOT NULL default '0.0000',
      `stock_status` tinyint(3) unsigned NOT NULL,
      PRIMARY KEY  (`product_id`,`website_id`,`stock_id`),
      CONSTRAINT `FK_AITQUANTITYMANAGER_STOCK_STATUS_STOCK` FOREIGN KEY (`stock_id`) REFERENCES `{$installer->getTable('cataloginventory_stock')}` (`stock_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT `FK_AITQUANTITYMANAGER_STOCK_STATUS_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    $installer->endSetup();
}