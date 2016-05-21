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
    "ALTER TABLE `$quoteTable` ADD msrp DECIMAL(12,4) DEFAULT NULL COMMENT 'MSRP';
    ALTER TABLE `$orderTable` ADD msrp DECIMAL(12,4) DEFAULT NULL COMMENT 'MSRP';"
);
