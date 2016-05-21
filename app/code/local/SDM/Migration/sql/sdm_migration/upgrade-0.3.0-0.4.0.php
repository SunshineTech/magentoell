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

/**
 * These are updates moved from SDM_Taxonomy's upgrade-0.1.0-0.2.0.php script.
 * It has been moved here as all catalog setup should be done in SDM_Migration.
 */
$this->updateAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'tag_category',
    array('source_model' => 'taxonomy/attribute_source_category')
);

$this->updateAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'tag_subcategory',
    array('source_model' => 'taxonomy/attribute_source_subcategory')
);

$this->updateAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'tag_theme',
    array('source_model' => 'taxonomy/attribute_source_theme')
);

$this->updateAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'tag_subtheme',
    array('source_model' => 'taxonomy/attribute_source_subtheme')
);

$this->updateAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'tag_curriculum',
    array('source_model' => 'taxonomy/attribute_source_curriculum')
);

$this->updateAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'tag_subcurriculum',
    array('source_model' => 'taxonomy/attribute_source_subcurriculum')
);

$this->updateAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'tag_product_line',
    array('source_model' => 'taxonomy/attribute_source_productline')
);

$this->updateAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'tag_subproduct_line',
    array('source_model' => 'taxonomy/attribute_source_subproductline')
);
