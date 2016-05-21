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

$quoteTable = $this->getTable('sales/quote');
$orderTable = $this->getTable('sales/order');

$this->run(
    "ALTER TABLE `$quoteTable` ADD saved_quote_id VARCHAR(16) DEFAULT NULL COMMENT 'Saved Quote Id' AFTER entity_id;
    ALTER TABLE `$orderTable` ADD saved_quote_id VARCHAR(16) DEFAULT NULL COMMENT 'Saved Quote Id' AFTER entity_id;"
);
