<?php
/**
 * Separation Degrees Media
 *
 * Install attribute sets, groups, and assign attributes
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Migration
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2014 Separation Degrees Media (http://www.separationdegrees.com)
 */

// Attribute for SDM_Compatibility
$this->updateAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'compatibility_product_line',
    array('source_model' => 'compatibility/source_productline')
);
