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

$this->run(
    "ALTER TABLE `sdm_sales_flat_saved_quote_item` 
        DROP COLUMN `pre_order_shipping_date`"
);

$this->run(
    "ALTER TABLE `sdm_sales_flat_saved_quote_item` 
        ADD `pre_order_shipping_dates` text COMMENT 'Pre-Order Shipping Date'
            AFTER `pre_order_release_date`"
);
