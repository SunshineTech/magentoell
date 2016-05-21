<?php
/**
 * Separation Degrees One
 *
 * Magento catalog customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Catalog
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$this->startSetup();

$this->updateAttribute('catalog_product', 'display_start_date', array(
    'input' => 'datetime',
    'frontend_input' => 'datetime'
));

$this->updateAttribute('catalog_product', 'display_end_date', array(
    'input' => 'datetime',
    'frontend_input' => 'datetime'
));

$this->endSetup();
