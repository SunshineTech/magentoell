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

$tableName = $this->getTable('customerdiscount/applied_discount');

$this->run("
    ALTER TABLE $tableName DROP INDEX `UNIQUE_APPLIED_DC_WEBSITE_ID_PRODUCT_ID`;
    ALTER TABLE $tableName ADD UNIQUE KEY `UNIQUE_APPLIED_DC_STORE_ID_PRODUCT_ID` (`store_id`,`product_id`);
");
