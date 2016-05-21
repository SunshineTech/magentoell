<?php
/**
 * Separation Degrees Media
 *
 * Install categories
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Migration
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2014 Separation Degrees Media (http://www.separationdegrees.com)
 */

$installer = $this;
$helper = Mage::helper('sdm_migration');
$websiteCodes = $helper->getWebsiteCodes();

// Change default root category name
Mage::getSingleton('core/resource')
    ->getConnection('core_write')
    ->query(
        "UPDATE `catalog_category_entity_varchar`
        SET value = '" . SDM_Migration_Helper_Data::WEBSITE_ROOT_CATEGORY_NAME_US . "'
        WHERE entity_type_id = 3 AND attribute_id = 41 AND store_id = 0 AND entity_id = 2"
    );

// Create "Catalog" categories for each root category, which is created as well if not found
foreach ($websiteCodes as $code => $name) {
    $rootCategory = $helper->getCategory($name, '1', '1');

    if (!$rootCategory->getId()) {
        $rootCategory = $helper->updateCategory($name, '1', '1', '1');  // Create root first
    }
    // Create/Update the level 2 category
    $helper->updateCategory(
        'Catalog',
        '2',
        $rootCategory->getId(),
        $rootCategory->getPath()
    );
}
