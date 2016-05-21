<?php
/**
 * Separation Degrees Media
 *
 * Ellison's Mage_Sales customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Sales
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

$quoteTable = $this->getTable('sales/quote_item');
$orderTable = $this->getTable('sales/order_item');

$this->run(
    "ALTER TABLE `$quoteTable` ADD item_type VARCHAR(255) DEFAULT NULL COMMENT 'Product Type';
    ALTER TABLE `$orderTable` ADD item_type VARCHAR(255) DEFAULT NULL COMMENT 'Product Type';"
);
