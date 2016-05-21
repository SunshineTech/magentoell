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
    ALTER TABLE $tableName DROP FOREIGN KEY FK_APPLIED_DC_WEB_ID_CORE_WEBSITE_ID;
    ALTER TABLE $tableName DROP COLUMN `website_id`;
    ALTER TABLE $tableName ADD COLUMN `store_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Store ID';
    ALTER TABLE $tableName ADD CONSTRAINT `FK_APPLIED_DC_STORE_ID_CORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE NO ACTION ON UPDATE CASCADE;
");
