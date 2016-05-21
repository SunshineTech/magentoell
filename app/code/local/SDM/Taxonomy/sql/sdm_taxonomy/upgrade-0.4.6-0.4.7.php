<?php
/**
 * Separation Degrees One
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

$taxonomyItem = $this->getTable('taxonomy/item');
$taxonomyProduct = $this->getTable('taxonomy/item_product');

$this->run("
    DROP TABLE IF EXISTS `$taxonomyProduct`;
    CREATE TABLE `$taxonomyProduct` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Record ID',
        `taxonomy_id` INT(11) UNSIGNED NOT NULL COMMENT 'Parent ID',
        `product_id` varchar(255) DEFAULT NULL COMMENT 'Product SKU',
        `sku` varchar(255) NOT NULL DEFAULT '' COMMENT 'SKU',
        `discount_type` varchar(255) NOT NULL COMMENT 'Discount Type Code',
        `discount_value` decimal(10,2) NOT NULL COMMENT 'Discount Value',
        `discount_price` decimal(10,2) NOT NULL COMMENT 'Rounded Calculated Discounted Price',
        PRIMARY KEY (`id`),
        UNIQUE KEY `UNIQUE_TAXONOMY_PRODUCT_TAG_ID_PROD_ID` (`taxonomy_id`,`product_id`),
        KEY `IDX_TAXONOMY_PRODUCT_TAG_ID` (`taxonomy_id`),
        KEY `IDX_TAXONOMY_PRODUCT_ID` (`product_id`),
        CONSTRAINT `FK_TAXONOMY_PRODUCT_TAG_ID` FOREIGN KEY (`taxonomy_id`) REFERENCES `$taxonomyItem` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
