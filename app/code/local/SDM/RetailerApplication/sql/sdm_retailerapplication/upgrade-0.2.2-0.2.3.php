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
$this->run("
    ALTER TABLE `{$this->getTable('retailerapplication/application')}`
        ADD `how_did_you_learn` varchar(4) DEFAULT NULL
");

$this->endSetup();
