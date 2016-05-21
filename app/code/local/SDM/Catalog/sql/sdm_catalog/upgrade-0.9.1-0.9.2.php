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

$config = new Mage_Core_Model_Config();

$this->updateAttribute('catalog_product', 'release_date', array(
    'used_in_product_listing' => true
));

$this->endSetup();
