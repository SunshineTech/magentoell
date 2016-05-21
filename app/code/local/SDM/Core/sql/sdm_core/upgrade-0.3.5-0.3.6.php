<?php
/**
 * Separation Degrees Media
 *
 * SDM's core extension
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
    'tag_curriculum',
    'tag_subcurriculum',
    'tag_designer',
    'tag_artist',
    'grade_level',
    'tag_machine_compatibility',
    'tag_material_compatibility',
    'tag_special',
    'price',
    // Below attributes aren't filterable but place them below everything just in case
    'tag_discount_category',
    'tag_event'
);

$order = 0;
foreach ($orders as $code) {
    $order += 100;
    $this->updateAttribute('catalog_product', $code, array(
        'position' => $order
    ));
}
