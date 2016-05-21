<?php
/**
 * Separation Degrees One
 *
 * Ellison's Mage_Customer customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Customer
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$customerGroupTable = $this->getTable('customer/customer_group');

$this->run(
    "ALTER TABLE $customerGroupTable ADD position INT(11) NOT NULL DEFAULT '0' COMMENT 'Group Position';
    ALTER TABLE $customerGroupTable ADD min_qty_override TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Min Qty Override Flag';"
);
