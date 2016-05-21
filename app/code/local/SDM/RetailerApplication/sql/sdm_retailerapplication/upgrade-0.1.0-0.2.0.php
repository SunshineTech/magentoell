<?php
/**
 * Separation Degrees One
 *
 * Manages the retailer application
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Customer
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$this->startSetup();

// Install main taxonomy table
$this->run("
    ALTER TABLE `{$this->getTable('customer/address_entity')}` ADD `is_editable` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Is Editable'
");

$this->endSetup();
