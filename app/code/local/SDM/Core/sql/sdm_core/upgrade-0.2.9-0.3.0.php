<?php
/**
 * Separation Degrees Media
 *
 * SDM's core extension
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Core
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */


$this->updateAttribute('catalog_product', 'description', array(
    'frontend_label' => 'Objective',
    'attribute_code' => 'objective'
));

$this->updateAttribute('catalog_product', 'short_description', array(
    'frontend_label' => 'Description',
    'attribute_code' => 'description'
));

// Remove 'objective' from 'Product' attribute set
Mage::getModel('catalog/product_attribute_set_api')
    ->attributeRemove(72, 9);
