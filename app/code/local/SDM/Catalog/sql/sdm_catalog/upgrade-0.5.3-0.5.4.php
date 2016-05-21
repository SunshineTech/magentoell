<?php
/**
 * Separation Degrees One
 *
 * Magento catalog customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Catalog
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$indexTable = $this->getTable('sdm_catalog/index_custom_price');
$catalogTable = $this->getTable('catalog/product');
$storeTable = $this->getTable('core/store');

$this->run("
    DROP TABLE IF EXISTS `$indexTable`;
    CREATE TABLE `$indexTable` (
        `entity_id` INT(10) UNSIGNED NOT NULL COMMENT 'Entity ID',
        `store_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Store ID',
        `price` DECIMAL(12,4) DEFAULT NULL COMMENT 'Price',
        `final_price` DECIMAL(12,4) DEFAULT NULL COMMENT 'Final Price',
        PRIMARY KEY (`entity_id`, `store_id`),
        KEY `IDX_SDM_CATALOG_PRODUCT_INDEX_PRICE_STORE_ID` (`store_id`),
        CONSTRAINT `FK_CAT_PRD_IDX_CUSTOM_PRICE_ENTT_ID_CAT_PRD_ENTT_ENTT_ID` FOREIGN KEY (`entity_id`) REFERENCES `$catalogTable` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `FK_CAT_PRD_IDX_CUSTOM_PRICE_ST_ID_CORE_ST_ST_ID` FOREIGN KEY (`store_id`) REFERENCES `$storeTable` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='SDM Catalog Product Custom Price Index Table';
");
