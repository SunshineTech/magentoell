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
    "ALTER TABLE `sdm_sales_flat_saved_quote` 
        ADD `sdm_shipping_surcharge` decimal(12,4) DEFAULT NULL 
            COMMENT 'Sdm Shipping Surcharge'
            AFTER `tax_amount`"
);

$this->run(
    "ALTER TABLE `sales_flat_quote` 
        ADD `sdm_shipping_surcharge_override` decimal(12,4) DEFAULT NULL 
            COMMENT 'Sdm Shipping Surcharge Override Amount'
            AFTER `ext_shipping_info`"
);
