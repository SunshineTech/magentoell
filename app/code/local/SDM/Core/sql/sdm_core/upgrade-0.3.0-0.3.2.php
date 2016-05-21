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

// Remove uneeded attributes
$this->removeAttribute('catalog_product', 'color');
$this->removeAttribute('catalog_product', 'manufacturer');

// Define the order of our layered nav attributes
$orders = array(
    'brand',
    'tag_category',
    'tag_subcategory',
    'tag_product_line',
    'tag_subproduct_line',
    'tag_theme',
    'tag_subtheme',
    'tag_artist',
    'tag_designer',
    'tag_machine_compatibility',
    'tag_material_compatibility',
    'tag_curriculum',
    'tag_subcurriculum',
    'tag_grade_level',
    'tag_discount_category',
    'tag_event',
    'tag_special',
    'price'
);

$order = 0;
foreach ($orders as $code) {
    $order += 100;
    $this->updateAttribute('catalog_product', $code, array(
        'position' => $order
    ));
}
