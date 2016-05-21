<?php
/**
 * Separation Degrees Media
 *
 * Installs required attributes
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Migration
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2014 Separation Degrees Media (http://www.separationdegrees.com)
 */

$installer = $this;
$entityType = $this->getCatalogEntityTypeId();   // numeric form of 'catalog_product'

/**
 * Multi-selects, temporarily when installing for first time; will get updated
 * in a later version when SDM_Taxonomy is complete.
 */
$varcharMultiAtts = array(
    'tag_category' => 'Category',
    'tag_subcategory' => 'Subcategory',
    'tag_theme' => 'Theme',
    'tag_subtheme' => 'Subtheme',
    'tag_curriculum' => 'Curriculum',
    'tag_subcurriculum' => 'Subcurriculum',
    'tag_product_line' => 'Product Line',
    'tag_subproduct_line' => 'Subproduct Line',
    /**
     * Attributes below don't have the corresponding models yet.
     * Note: Upate this comment and names as more models are created.
     */
    'tag_machine_compatibility' => 'Machine Compatibility',     // Not part of taxonomy
    'tag_material_compatibility' => 'Material Compatibility',   // Not part of taxonomy
    'tag_artist' => 'Artist (Temporary)',
    'tag_designer' => 'Designer (Temporary)',
    'calendar_event' => 'Calendar Events (Temporary)',
    'grade_level' => 'Grade Level',
);

$sortOrder = 10;
foreach ($varcharMultiAtts as $code => $name) {
    $attributeId = $this->getAttributeId($entityType, $code);
    // if (isset($attributeId) && !empty($attributeId)) {
    //     continue;
    // }

    $data = array(
        'type' => 'varchar',
        'backend' => 'eav/entity_attribute_backend_array',
        'frontend' => '',
        'label' => $name,
        'input' => 'multiselect',
        'class' => '',
        'source' => '',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => true,
        'required' => false,
        'user_defined' => true,
        'default' => '',
        'searchable' => '0',
        'filterable' => false,    // yes w/ results
        'filterable_in_search' => '1',
        'comparable' => false,
        'visible_on_front' => false,
        'unique' => false,
        'is_html_allowed_on_front' => true,
        'is_configurable' => false,
        'apply_to' => 'simple,grouped', // comma-delimited
        'sort_order' =>  $sortOrder,
    );
    $installer->addAttribute($entityType, $code, $data);
    $sortOrder += 10;
}

/**
 * Int-selects
 */
$intSelectAtts = array(
    'brand' => 'Brand',
    'life_cycle' => 'Life Cycle',
    'product_type' => 'Product Type',
    'die_size' => 'Die Size',
    'release_date' => 'Release Date',
    'compatibility_product_line' => 'Compatibility Product Line',
);

$sortOrder = 10;
foreach ($intSelectAtts as $code => $name) {
    $attributeId = $this->getAttributeId($entityType, $code);
    // if (isset($attributeId) && !empty($attributeId)) {
    //     continue;
    // }

    $data = array(
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => $name,
        'input' => 'select',
        'class' => '',
        'source' => 'eav/entity_attribute_source_table',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => true,
        'required' => false,
        'user_defined' => true,
        'default' => '',
        'searchable' => '0',
        'filterable' => false,    // yes w/ results
        'filterable_in_search' => '1',
        'comparable' => false,
        'visible_on_front' => false,
        'unique' => false,
        'is_html_allowed_on_front' => true,
        'is_configurable' => false,
        'apply_to' => 'simple,grouped', // comma-delimited
        'sort_order' =>  $sortOrder,
    );
    $installer->addAttribute($entityType, $code, $data);
    $sortOrder += 10;
}


/**
 * Booleans
 */
$boolAtts = array(
    'homefeed_product' => 'Homefeed Product',
    'in_store' => 'In Store',
    'purchase_hold' => 'Purchase Hold',
    'is_orderable' => 'Orderable',
);

$sortOrder = 10;
foreach ($boolAtts as $code => $name) {
    $attributeId = $this->getAttributeId($entityType, $code);
    // if (isset($attributeId) && !empty($attributeId)) {
    //     continue;
    // }

    $data = array(
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => $name,
        'input' => 'boolean',
        'class' => '',
        'source' => 'eav/entity_attribute_source_boolean',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => true,
        'required' => false,
        'user_defined' => true,
        'default' => '0',
        'searchable' => '0',
        'filterable' => false,    // yes w/ results
        'filterable_in_search' => '0',
        'comparable' => false,
        'visible_on_front' => false,
        'unique' => false,
        'is_html_allowed_on_front' => true,
        'is_configurable' => false,
        'apply_to' => 'simple,grouped', // comma-delimited
    );
    $installer->addAttribute($entityType, $code, $data, $sortOrder);
    $sortOrder += 10;
}


/**
 * Varchars
 */
$varcharAtts = array(
    'upc' => 'UPC',  // available from "Details"
    'instruction_file' => 'Instruction File Path',
    'md5_hash' => 'MD5 Hash (Temporary field. Do not edit)',
    'availalbility_message' => 'Availability Message',
    /**
     * "Details" tab attributes - start
     * These are the revised set of attribute that Ellison will work on manually.
     * Therefore, they will get no data in the migration.
     */
    'product_dimensions' => 'Product Dimensions',
    'design_dimensions' => 'Design Dimensions',
    'includes' => 'Includes',
    'construction' => 'Construction',
    'product_weight' => 'Product Weight',
    'packaged_weight' => 'Packaged Weight',
    'machine_weight' => 'Machine Weight',
    'machine_dimensions' => 'Machine Dimensions',
    'warranty' => 'Warranty',
    'related_accessories' => 'Related Accessories (comma-delimited)',
    /**
     * "Details" tab attributes - end
     */
);

$sortOrder = 10;
foreach ($varcharAtts as $code => $name) {
    $attributeId = $this->getAttributeId($entityType, $code);
    // if (isset($attributeId) && !empty($attributeId)) {
    //     continue;
    // }

    $data = array(
        'type' => 'varchar',
        'backend' => '',
        'frontend' => '',
        'label' => $name,
        'input' => 'text',
        'class' => '',
        'source' => '',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => true,
        'required' => false,
        'user_defined' => true,
        'default' => '',
        'searchable' => '0',
        'filterable' => false,    // yes w/ results
        'filterable_in_search' => '0',
        'comparable' => false,
        'visible_on_front' => false,
        'unique' => false,
        'is_html_allowed_on_front' => true,
        'is_configurable' => false,
        'apply_to' => 'simple,grouped', // comma-delimited
        'sort_order' =>  $sortOrder,
    );
    $installer->addAttribute($entityType, $code, $data);
    $sortOrder += 10;
}


/*
 * Texts
 */
$textAtts = array(
    'idea_introduction' => 'Introduction',
    'idea_standards' => 'Standards',
    'idea_instructions' => 'Instructions',
    'idea_instruction_images' => 'Images (serialized array)'
);

$sortOrder = 10;
foreach ($textAtts as $code => $name) {
    $attributeId = $this->getAttributeId($entityType, $code);
    // if (isset($attributeId) && !empty($attributeId)) {
    //     continue;
    // }

    $data = array(
        'type' => 'text',
        'backend' => '',
        'frontend' => '',
        'label' => $name,
        'input' => 'textarea',
        'class' => '',
        'source' => '',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => true,
        'required' => false,
        'user_defined' => true,
        'default' => '',
        'searchable' => '0',
        'filterable' => false,    // yes w/ results
        'filterable_in_search' => '0',
        'comparable' => false,
        'visible_on_front' => false,
        'unique' => false,
        'is_html_allowed_on_front' => true,
        'is_configurable' => false,
        'apply_to' => 'simple,grouped', // comma-delimited
        'sort_order' =>  $sortOrder,
    );
    $installer->addAttribute($entityType, $code, $data);
    $sortOrder += 10;
}

/**
 * Datetimes
 */
$textAtts = array(
    'display_start_date' => 'Display Start',
    'display_end_date' => 'Dispay End',
    'virtual_weight_end_date' => 'Virtual Weight End Data',
);

$sortOrder = 10;
foreach ($textAtts as $code => $name) {
    $attributeId = $this->getAttributeId($entityType, $code);
    // if (isset($attributeId) && !empty($attributeId)) {
    //     continue;
    // }

    $data = array(
        'type' => 'datetime',
        'backend' => 'eav/entity_attribute_backend_datetime',
        'frontend' => 'eav/entity_attribute_frontend_datetime',
        'label' => $name,
        'input' => 'date',
        'class' => '',
        'source' => '',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => true,
        'required' => false,
        'user_defined' => true,
        'default' => '',
        'searchable' => '0',
        'filterable' => false,    // yes w/ results
        'filterable_in_search' => '0',
        'comparable' => false,
        'visible_on_front' => false,
        'unique' => false,
        'is_html_allowed_on_front' => false,
        'is_configurable' => false,
        'apply_to' => 'simple,grouped', // comma-delimited
        'sort_order' =>  $sortOrder,
    );
    $installer->addAttribute($entityType, $code, $data);
    $sortOrder += 10;
}

/**
 * Decimals: Prices, weights, etc.
 */
$textAtts = array(
    'handling_fee' => 'Handling Fee',
    'wholesale_price' => 'Wholesale Price',
    'virtual_weight' => 'Virtual Weight',
);

$sortOrder = 10;
foreach ($textAtts as $code => $name) {
    $attributeId = $this->getAttributeId($entityType, $code);
    // if (isset($attributeId) && !empty($attributeId)) {
    //     continue;
    // }

    $data = array(
        'type' => 'varchar',
        'backend' => '',
        'frontend' => '',
        'label' => $name,
        'input' => 'text',
        'class' => '',
        'frontend_class' => 'validate-number',
        'source' => '',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => true,
        'required' => false,
        'user_defined' => true,
        'default' => '',
        'searchable' => '0',
        'filterable' => false,    // yes w/ results
        'filterable_in_search' => '0',
        'comparable' => false,
        'visible_on_front' => false,
        'unique' => false,
        'is_html_allowed_on_front' => false,
        'is_configurable' => false,
        'apply_to' => 'simple,grouped', // comma-delimited
        'sort_order' =>  $sortOrder,
    );
    $installer->addAttribute($entityType, $code, $data);
    $sortOrder += 10;
}

/**
 * Update attributes
 */

// Required
$required = array(
    'life_cycle',
    'brand',
    'product_type',
);
foreach ($required as $att) {
    $installer->updateAttribute(
        'catalog_product',
        $this->getAttributeId($entityType, $att),
        'is_required',
        true
    );
}

// Not required
$notRequired = array(
    'description',
    'short_description',
);
foreach ($notRequired as $att) {
    $installer->updateAttribute(
        'catalog_product',
        $this->getAttributeId($entityType, $att),
        'is_required',
        false
    );
}

// Scopes
$websiteScope = array(
    'display_start_date',
    'display_end_date',
    'description',
    'short_description',
    'is_orderable',
    'availalbility_message',
    'virtual_weight',
    'virtual_weight_end_date',
    'tag_category',
    'tag_subcategory',
    'tag_theme',
    'tag_subtheme',
    'tag_curriculum',
    'tag_subcurriculum',
    'tag_product_line',
    'tag_subproduct_line',
    'compatibility_product_line',
    'tag_machine_compatibility',
    'tag_material_compatibility',
    // Madhavi stated that below tab data are of global scope
    // 'accessories',
    // 'idea_introduction',
    // 'idea_standards',
    // 'idea_instructions',
    // 'idea_instruction_images',
);

foreach ($websiteScope as $att) {
    $installer->updateAttribute(
        'catalog_product',
        $this->getAttributeId($entityType, $att),
        'is_global',
        Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE
    );
}

// Layered navigation-searchable
$filterables = array(
    'tag_category',
    'tag_subcategory',
    'tag_theme',
    'tag_subtheme',
    'tag_curriculum',
    'tag_subcurriculum',
    'tag_product_line',
    'tag_subproduct_line',
    'tag_machine_compatibility',
    'tag_material_compatibility',
    'tag_artist',
    'tag_designer',
    'grade_level',
    'product_type',
    'die_size',
);
foreach ($filterables as $att) {
    $installer->updateAttribute(
        'catalog_product',
        $this->getAttributeId($entityType, $att),
        'is_filterable',
        '1'
    );

    // is_filterable_in_search
}

// Simple products only
$simples = array(
    'compatibility_product_line',
    // 'tag_machine_compatibility',
    // 'tag_material_compatibility',
);
foreach ($simples as $att) {
    $installer->updateAttribute(
        'catalog_product',
        $this->getAttributeId($entityType, $att),
        'apply_to',
        'simple'
    );

    // is_filterable_in_search
}

/**
 * Columns from 'Details' tab in Ellison. These will mapped and consolidated
 * manually by Ellison and updated with Dataflow later on.
 */
// $details = array(
//     'designer' => 'Designer',
//     'upc' => 'UPC',
//     'pack_size' => 'Pack Size',
//     'individual_die_titles' => 'Individual Die Titles',
//     'weight' => 'weight',
//     'construction' => 'construction',
//     'blank_size' => 'blank size',
//     'material' => 'material',
//     'vintaj_item_number' => 'vintaj item number',
//     'size' => 'size',
//     'active' => 'active',
//     'text' => 'text',
//     'Warranty' => 'Warranty',
//     'Stencil_Size' => 'Stencil Size',
//     'Die_Block_Size' => 'Die Block Size',
//     'Design_Measurements' => 'Design Measurements',
//     'Individual_Plate_Titles' => 'Individual Plate Titles',
//     'Embossing_Plate_Size' => 'Embossing Plate Size',
//     'Sheet_Size' => 'Sheet Size',
//     'Adapter_Size' => 'Adapter Size',
//     'Cutting_Pad_Size' => 'Cutting Pad Size',
//     'Machine_Size' => 'Machine Size',
//     'Converter_Size' => 'Converter Size',
//     'Bag_Size' => 'Bag Size',
//     'Shipping_and_Handling_Surcharge' => 'Shipping and Handling Surcharge',
//     'Stamp_Mount_Size' => 'Stamp Mount Size',
//     'Platform_Size' => 'Platform Size',
//     'Crease_Pad_Size' => 'Crease Pad Size',
//     'Folder_Size' => 'Folder Size',
//     'Silicone_Rubber_Size' => 'Silicone Rubber Size',
//     'Impressions_Pad_Size' => 'Impressions Pad Size',
//     'Instructions' => 'Instructions',
//     'Storage_Box_Size' => 'Storage Box Size',
//     'Tote_Size' => 'Tote_Size',
//     'Handbag_Size' => 'Handbag Size',
//     'Workstation_Size' => 'Workstation Size',
//     'Storage_Rack_Size' => 'Storage Rack Size',
//     'Individual_Folder_Titles' => 'Individual Folder Titles',
//     'Embossing_Folder_Size' => 'Embossing Folder Size',
//     'Measurements' => 'Measurements',
//     'Embossing_Pad_Size' => 'Embossing Pad Size',
//     'Beginners_Kit_includes' => 'Beginners Kit includes',
//     'Contents' => 'Contents',
//     'Adapter_Pad_Size' => 'Adapter Pad Size',
//     'Plastic_Slide_Size' => 'Plastic Slide Size',
//     'Sliding_Tray_Size' => 'Sliding Tray Size',
//     'Mylar_Shim_Size' => 'Mylar Shim Size',
//     'Starter_Kit_includes' => 'Starter Kit includes',
//     'Cutting_Mat_Size' => 'Cutting Mat Size',
//     'Cable_Size' => 'Cable Size',
//     'Tool_Case_Size' => 'Tool Case Size',
//     'Adhesive_Sheet_Size' => 'Adhesive Sheet Size',
//     'Bonus_Kit_includes' => 'Bonus Kit includes',
//     'Tray_Size' => 'Tray Size',
//     'Solo_Platform_Shim_Size' => 'Solo Platform Shim_Size',
//     'Des' => 'Des',
//     'Solo_Shim_Size' => 'Solo Shim Size',
//     'Embossing_Folder_Titles' => 'Embossing Folder Titles',
//     'Letterpress_Plate_Size' => 'Letterpress Plate Size',
//     'Platform_Shim_Adapter_Size' => 'Platform Shim Adapter Size',
//     'Kit_includes' => 'Kit Includes',
//     'Link_Size' => 'Link Size',
//     'Template_Size' => 'Template Size',
//     'Pack_Size87' => 'Pack Size',
//     'Label_Size' => 'Label Size',
//     'Archival_of_audio_memories' => 'Archival of Audio Memories',
//     'Individual_Titles' => 'Individual Titles',
//     'Design_Size_Measrement' => 'Design Size Measrement',
//     'Design_Measurement' => 'Design Measurement',
//     'Stamping_Piercing_Mat_Size' => 'Stamping Piercing Mat Size',
//     'Paper_Piercer_Size' => 'Paper Piercer Size',
// );

// $sortOrder = 10;
// foreach ($details as $code => $name) {
//     $attributeId = $this->getAttributeId($entityType, $code);
//     // if (isset($attributeId) && !empty($attributeId)) {
//     //     continue;
//     // }

//     $data = array(
//         'type' => 'varchar',
//         'backend' => '',
//         'frontend' => '',
//         'label' => $name,
//         'input' => 'text',
//         'class' => '',
//         'source' => '',
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
//         'is_html_allowed_on_front' => true,
//         'is_configurable' => false,
//         'apply_to' => 'simple,grouped', // comma-delimited
//         'sort_order' =>  $sortOrder,
//     );
//     $installer->addAttribute($entityType, $code , $data);
//     $sortOrder += 10;
// }
