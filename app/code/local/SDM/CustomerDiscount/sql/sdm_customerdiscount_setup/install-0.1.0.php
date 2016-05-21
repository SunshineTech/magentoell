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

$this->run("
DROP TABLE IF EXISTS `{$this->getTable('customerdiscount/discountgroup')}`;
CREATE TABLE `{$this->getTable('customerdiscount/discountgroup')}` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Record ID',
    `category_id` INT(11) UNSIGNED DEFAULT NULL COMMENT 'Category Taxonomy ID',
    `customer_group_id` SMALLINT(5) UNSIGNED DEFAULT NULL COMMENT 'Customer Group ID',
    `amount` TINYINT(3) DEFAULT NULL COMMENT 'Discount Amount',
    `created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Creation Time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Update Time',
    PRIMARY KEY (`id`),
    KEY `IDX_CUSTOMER_GROUP_DISCOUNT_GROUP_ID` (`customer_group_id`),
    KEY `IDX_CUSTOMER_GROUP_DISCOUNT_CATEGORY_ID` (`category_id`),
    CONSTRAINT `FK_CUSTOMER_GROUP_DISCOUNT_GROUP_ID_CUSTOMER_GROUP_ID` FOREIGN KEY (`customer_group_id`) REFERENCES `{$this->getTable('customer/customer_group')}` (`customer_group_id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='Ellison Customer Group Discount';
");
