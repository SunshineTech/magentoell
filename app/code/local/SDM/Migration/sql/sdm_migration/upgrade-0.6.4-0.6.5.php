<?php
/**
 * Separation Degrees Media
 *
 * Ellison's custom product taxonomy implementation.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Taxonomy
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

$entityTypeId = $this->getCatalogEntityTypeId();   // numeric form of 'catalog_product'

$attributeId = $this->addAttribute(
    $entityTypeId,
    'tag_special',
    array(
        'type' => 'varchar',
        'backend' => 'eav/entity_attribute_backend_array',
        'frontend' => '',
        'label' => 'Special',
        'input' => 'multiselect',
        'class' => '',
        'source' => 'taxonomy/attribute_source_special',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => true,
        'required' => false,
        'user_defined' => true,
        'default' => '',
        'searchable' => '0',
        'filterable' => 1,    // yes w/ results
        'filterable_in_search' => '0',
        'comparable' => false,
        'visible_on_front' => false,
        'unique' => false,
        'is_html_allowed_on_front' => true,
        'is_configurable' => false,
        'apply_to' => 'simple', // comma-delimited
        'sort_order' =>  140,
    )
);

$this->addAttributeToSet(
    $entityTypeId,  // int
    $this->createAttributeSet('Product'),
    'Taxonomy',
    'tag_special',
    140    // sort order
);
