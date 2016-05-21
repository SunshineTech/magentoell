<?php
/**
 * Separation Degrees Media
 *
 * Implements the product compatibility functionality.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Compatibility
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('compatibility/productline')}` ADD `rich_description` text DEFAULT NULL COMMENT 'Rich Description'
");

$this->endSetup();
