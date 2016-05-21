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

$entityTypeId = $this->getCatalogEntityTypeId();   // numeric form of 'catalog_product'

// No longer using this attribute. Using image label instead to assign instruction images.
$this->run(
    "DELETE FROM `{$this->getTable('eav/attribute')}` WHERE attribute_code = 'idea_instruction_image'"
);

// Make product and subproduct lines multiselect
$this->updateAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'tag_product_line',
    array(
        'source_model' => 'taxonomy/attribute_source_productline',
        'backend_type' => 'varchar',
        'frontend_input' => 'multiselect',
        'backend_model' => 'eav/entity_attribute_backend_array',
        'is_required' => false,
        // 'user_defined' => true,
    )
);
$this->updateAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'tag_subproduct_line',
    array(
        'source_model' => 'taxonomy/attribute_source_subproductline',
        'backend_type' => 'varchar',
        'frontend_input' => 'multiselect',
        'backend_model' => 'eav/entity_attribute_backend_array',
        'is_required' => false,
    )
);

// This updates, but attribute doesn't function properly.
// $this->updateAttribute(
//     Mage_Catalog_Model_Product::ENTITY,
//     'idea_instruction_image',
//     array(
//         'backend_type' => 'varchar',
//         'frontend_model' => 'catalog/product_attribute_frontend_image',
//         'frontend_input' => 'media_image',
//         'frontend_label' => 'Instruction Image',
//         'apply_to' => null,
//         // 'user_defined' => true,
//     )
// );

// $this->addAttribute(
//     Mage_Catalog_Model_Product::ENTITY,
//     'idea_instruction_image',
//     array(
//         'group' => 'Images',
//         'label' => 'Instruction Image',
//         'input' => 'media_image',
//         'type' => 'varchar',
//         'backend' => '',
//         'frontend' => 'catalog/product_attribute_frontend_image',
//         'class' => '',
//         'source' => null,
//         'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
//         'visible' => true,
//         'required' => false,
//         'user_defined' => true,
//         'default' => '',
//         'searchable' => '0',
//         'filterable' => false,    // yes w/ results
//         'filterable_in_search' => '0',
//         'comparable' => false,
//         'visible_on_front' => false,
//         'unique' => false,
//         'is_html_allowed_on_front' => false,
//         'is_configurable' => true,
//         'apply_to' => null,
//         'sort_order' => 100,
//     )
// );
