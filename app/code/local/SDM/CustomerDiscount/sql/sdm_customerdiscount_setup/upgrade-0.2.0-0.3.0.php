<?php
/**
 * Separation Degrees One
 *
 * Implements the customer/retailer discount logic for viewing and obtaining
 * discounts and prices, as wel as managing the customer groups.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CustomerDiscount
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$tableName = $this->getTable('customerdiscount/applied_discount');

$this->run("
DROP TABLE IF EXISTS `$tableName`;
CREATE TABLE `$tableName` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Record ID',
    `website_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Website ID',
    `product_id` INT(10) UNSIGNED NOT NULL COMMENT 'Product ID',
    `type` VARCHAR(255) DEFAULT NULL COMMENT 'Applied Discount Type Code',
    PRIMARY KEY (`id`),
    UNIQUE KEY `UNIQUE_APPLIED_DC_WEBSITE_ID_PRODUCT_ID` (`website_id`,`product_id`),
    KEY `IDX_APPLIED_DISCOUNT_WEBSITE_ID` (`website_id`),
    KEY `IDC_APPLIED_DISCOUNT_PRODUCT_ID` (`product_id`),
    CONSTRAINT `FK_APPLIED_DC_WEB_ID_CORE_WEBSITE_ID` FOREIGN KEY (`website_id`) REFERENCES `{$this->getTable('core/website')}` (`website_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
    CONSTRAINT `FK_APPLIED_DC_PRODUCT_ID_CATALOG_ENTITY_PRODUCT_ID` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog/product')}` (`entity_id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8;
;
");
