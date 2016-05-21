<?php
/**
 * Separation Degrees One
 *
 * Ellison and Sizzix Shipping Rules
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Shipping
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/* @var $installer Mage_Sales_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
    TRUNCATE  `{$this->getTable('sdm_shipping/rate_eu')}`;
");

$installer->endSetup();
