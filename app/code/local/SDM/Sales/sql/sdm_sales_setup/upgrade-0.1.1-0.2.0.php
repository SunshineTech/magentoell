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

$table = $this->getTable('sdm_sales/order_ax');

$this->run("
    DROP TABLE IF EXISTS `$table`;
    CREATE TABLE `$table` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `parent_id` int(10) unsigned NOT NULL,
        `number` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `UNQ_SALES_ORDER_AX_PARENT_ID` (`parent_id`),
        KEY `FK_AX_ID_ORDER_ID` (`parent_id`),
        CONSTRAINT `FK_AX_ID_ORDER_ID` FOREIGN KEY (`parent_id`) REFERENCES `sales_flat_order` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
