<?php
/**
 * Separation Degrees One
 *
 * Install attribute sets, groups, and assign attributes
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Migration
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2014 Separation Degrees One (http://www.separationdegrees.com)
 */

// First, clean up all categories except the root and "Catalog" categories
Mage::register('isSecureArea', true);   // Allows category deletion
$categories = $this->getAllNonBaseCategories();
foreach ($categories as $category) {
    $category->delete();
}
Mage::unregister('isSecureArea');

// Make all "Catalog" category to be not visible in the navigation menu
$allcatelogCategories = $this->getAllCatalogCategories();
$tableName = $this->getTable('catalog/category') . '_int';
foreach ($allcatelogCategories as $category) {
    // Unable to save only admin store level data
    $q1 = "DELETE
        FROM $tableName
        WHERE store_id != 0
            AND `attribute_id` = 67
            AND `entity_id` = {$category->getId()};";
    $q2 = "UPDATE $tableName
        SET `value` = 0
        WHERE store_id = 0
            AND `attribute_id` = 67
            AND `entity_id` = {$category->getId()};";

    $this->run($q1);
    $this->run($q2);
}
