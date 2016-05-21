<?php
/**
 * Separation Degrees One
 *
 * Ellison's negotiated product prices
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_NegotiatedProduct
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$this->run("
    DROP TABLE IF EXISTS `{$this->getTable('negotiatedproduct/negotiatedproduct')}`;
    CREATE TABLE `{$this->getTable('negotiatedproduct/negotiatedproduct')}` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Record ID',
        `website_id` int(11) NOT NULL COMMENT 'Website ID',
        `customer_id` int(10) unsigned NOT NULL COMMENT 'Customer ID',
        `product_id` int(11) unsigned NOT NULL COMMENT 'Product ID',
        `sku` varchar(255) NOT NULL COMMENT 'SKU',
        `price` decimal(12,4) DEFAULT NULL COMMENT 'Negotiated Price',
        PRIMARY KEY (`id`),
        UNIQUE KEY `website_id` (`website_id`,`customer_id`,`product_id`),
        KEY `IDX_CUSTOMER_NEGOTIATED_PRODUCT_CUSTOMER_ID` (`customer_id`),
        KEY `IDX_CUSTOMER_NEGOTIATED_PRODUCT_ID` (`product_id`),
        KEY `IDX_CUS_WEBSITE_ID` (`website_id`),
        CONSTRAINT `FK_CUSTOMER_ENTITY_NEGOTIATED_PRODUCT_CUSTOMER_ID` FOREIGN KEY (`customer_id`) REFERENCES `{$this->getTable('customer/entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=INNODB DEFAULT CHARSET=utf8;
");
