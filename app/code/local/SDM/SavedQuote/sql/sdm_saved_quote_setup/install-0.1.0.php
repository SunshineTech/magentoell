<?php
/**
 * Separation Degrees Media
 *
 * Allows saving quotes that can be later be converted into orders with preserved
 * pricing.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_SavedQuote
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

$this->startSetup();    // disable integrity check for DROP statements

// Install main saved quote table
$this->run(
    "DROP TABLE IF EXISTS `{$this->getTable('savedquote/savedquote')}`;
    CREATE TABLE `{$this->getTable('savedquote/savedquote')}` (
        `entity_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Entity ID',
        `quote_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Quote ID',
        `store_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Store ID',
        `is_active` SMALLINT(5) UNSIGNED DEFAULT '1' COMMENT 'Is Active',
        `increment_id` VARCHAR(50) DEFAULT NULL COMMENT 'Increment Id',
        `name` VARCHAR(255) DEFAULT NULL COMMENT 'Quote Name',
        `customer_id` INT(10) UNSIGNED DEFAULT '0' COMMENT 'Customer ID',
        `customer_tax_class_id` INT(10) UNSIGNED DEFAULT '0' COMMENT 'Customer Tax Class Id',
        `customer_group_id` INT(10) UNSIGNED DEFAULT '0' COMMENT 'Customer Group Id',
        `customer_email` VARCHAR(255) DEFAULT NULL COMMENT 'Customer Email',
        `customer_prefix` VARCHAR(40) DEFAULT NULL COMMENT 'Customer Prefix',
        `customer_firstname` VARCHAR(255) DEFAULT NULL COMMENT 'Customer Firstname',
        `customer_middlename` VARCHAR(40) DEFAULT NULL COMMENT 'Customer Middlename',
        `customer_lastname` VARCHAR(255) DEFAULT NULL COMMENT 'Customer Lastname',
        `customer_suffix` VARCHAR(40) DEFAULT NULL COMMENT 'Customer Suffix',
        `customer_note` VARCHAR(255) DEFAULT NULL COMMENT 'Customer Note',
        `coupon_codes` varchar(255) DEFAULT NULL COMMENT 'Coupon Codes',
        `carrier` VARCHAR(255) DEFAULT NULL COMMENT 'Carrier',
        `carrier_title` VARCHAR(255) DEFAULT NULL COMMENT 'Carrier Title',
        `shipping_code` VARCHAR(255) DEFAULT NULL COMMENT 'Code',
        `shipping_method` VARCHAR(255) DEFAULT NULL COMMENT 'Method',
        `subtotal` DECIMAL(12,4) DEFAULT NULL COMMENT 'Subtotal',
        `discount` DECIMAL(12,4) DEFAULT NULL COMMENT 'Discount',
        `shipping_cost` DECIMAL(12,4) DEFAULT NULL COMMENT 'Quoted Shipping Cost',
        `tax_amount` DECIMAL(12,4) DEFAULT NULL COMMENT 'Tax Amount',
        `grand_total` DECIMAL(12,4) DEFAULT NULL COMMENT 'Grand Total',
        `converted_at` timestamp NULL DEFAULT NULL COMMENT 'Converted At',
        `expires_at` timestamp NULL DEFAULT NULL COMMENT 'Expires At',
        `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Created At',
        `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Updated At',
        PRIMARY KEY (`entity_id`),
        KEY `IDX_SALES_FLAT_SQUOTE_INCREMENT_ID` (`increment_id`),
        CONSTRAINT `FK_SALES_FLAT_SQUOTE_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `FK_SALES_FLAT_SQUOTE_CUSTOMER_ID_CUSTOMER_ENTITY_ID` FOREIGN KEY (`customer_id`) REFERENCES `customer_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='Ellison Saved Quote';"
);

// Install the saved quote item table
$this->run(
    "DROP TABLE IF EXISTS `{$this->getTable('savedquote/savedquote_item')}`;
    CREATE TABLE `{$this->getTable('savedquote/savedquote_item')}` (
        `item_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Item ID',
        `saved_quote_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Quote ID',
        `product_id` INT(10) UNSIGNED DEFAULT NULL COMMENT 'Product ID',
        `store_id` SMALLINT(5) UNSIGNED DEFAULT NULL COMMENT 'Store ID',
        `sku` VARCHAR(255) DEFAULT NULL COMMENT 'SKU',
        `name` VARCHAR(255) DEFAULT NULL COMMENT 'Name',
        `qty` DECIMAL(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Qty',
        -- `image_url` VARCHAR(255) DEFAULT NULL COMMENT 'Image URL',
        `product_type` VARCHAR(255) DEFAULT NULL COMMENT 'Product Type',
        `item_options` TEXT COMMENT 'Item Options',
        `price` DECIMAL(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Price',
        `tax_percent` DECIMAL(12,4) DEFAULT '0.0000' COMMENT 'Tax Percent',
        `tax_amount` DECIMAL(12,4) DEFAULT '0.0000' COMMENT 'Tax Amount',
        `row_total` DECIMAL(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Row Total',  -- prior to tax, includes discount
        `discount_amount` DECIMAL(12,4) DEFAULT NULL COMMENT 'Discount Amount',
        -- `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Created At',
        -- `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Updated At',
        PRIMARY KEY (`item_id`),
        KEY `IDX_SALES_FLAT_QUOTE_ITEM_PRODUCT_ID` (`product_id`),
        KEY `IDX_SALES_FLAT_QUOTE_ITEM_QUOTE_ID` (`saved_quote_id`),
        CONSTRAINT `FK_SALES_FLAT_SQUOTE_ITEM_PRD_ID_CAT_PRD_ENTT_ENTT_ID` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog/product')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `FK_SALES_FLAT_SQUOTE_ITEM_QUOTE_ID_SALES_FLAT_SQUOTE_ENTITY_ID` FOREIGN KEY (`saved_quote_id`) REFERENCES `{$this->getTable('savedquote/savedquote')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `FK_SALES_FLAT_SQUOTE_ITEM_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='Ellison Saved Quote Item';"
);


// Install the saved quote address table
$this->run(
    "DROP TABLE IF EXISTS `{$this->getTable('savedquote/savedquote_address')}`;
    CREATE TABLE `{$this->getTable('savedquote/savedquote_address')}` (
        `address_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Address ID',
        `saved_quote_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Quote ID',
        -- `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Created At',
        -- `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Updated At',
        `address_type` VARCHAR(255) DEFAULT NULL COMMENT 'Address Type',
        `prefix` VARCHAR(40) DEFAULT NULL COMMENT 'Prefix',
        `firstname` VARCHAR(255) DEFAULT NULL COMMENT 'Firstname',
        `middlename` VARCHAR(40) DEFAULT NULL COMMENT 'Middlename',
        `lastname` VARCHAR(255) DEFAULT NULL COMMENT 'Lastname',
        `suffix` VARCHAR(40) DEFAULT NULL COMMENT 'Suffix',
        `company` VARCHAR(255) DEFAULT NULL COMMENT 'Company',
        `street` VARCHAR(255) DEFAULT NULL COMMENT 'Street',
        `city` VARCHAR(255) DEFAULT NULL COMMENT 'City',
        `region` VARCHAR(255) DEFAULT NULL COMMENT 'Region',
        `region_id` INT(10) UNSIGNED DEFAULT NULL COMMENT 'Region ID',
        `postcode` VARCHAR(255) DEFAULT NULL COMMENT 'Postcode',
        `country_id` VARCHAR(255) DEFAULT NULL COMMENT 'Country ID',
        `telephone` VARCHAR(255) DEFAULT NULL COMMENT 'Telephone',
        `fax` VARCHAR(255) DEFAULT NULL COMMENT 'Fax',
        `same_as_billing` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Same As Billing',
    PRIMARY KEY (`address_id`),
    KEY `IDX_ELLISON_SALES_FLAT_SQUOTE_ADDRESS_QUOTE_ID` (`saved_quote_id`),
    CONSTRAINT `FK_ELLISON_SALES_FLAT_SQUOTE_ADDRESS_QUOTE_ID_SQUOTE_ENTITY_ID` FOREIGN KEY (`saved_quote_id`) REFERENCES `{$this->getTable('savedquote/savedquote')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='Ellison Sales Flat Saved Quote Address';"
);
$this->endSetup();
