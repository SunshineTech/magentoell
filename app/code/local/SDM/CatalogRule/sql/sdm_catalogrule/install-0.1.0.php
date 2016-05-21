<?php
/**
 * Separation Degrees One
 *
 * Magento catalog rule customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CatalogRule
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$this->startSetup();

$this->run("
    ALTER TABLE  `{$this->getTable('catalogrule/rule')}`
        ADD COLUMN `hide_sale_icon` tinyint(3) DEFAULT NULL,
        ADD COLUMN `custom_sale_icon` varchar(64) DEFAULT NULL
");

$this->endSetup();
