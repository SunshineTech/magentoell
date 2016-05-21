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

// Re-install this attribute.
$this->run(
    "DELETE FROM `{$this->getTable('eav/attribute')}` WHERE attribute_code = 'compatibility_product_line'"
);

$attributeId = $this->addAttribute(
    $entityTypeId,
    'compatibility_product_line',
    array(
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => 'Compatibility Product Line',
        'input' => 'select',
        'class' => '',
        'source' => 'compatibility/source_productline',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
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
        'apply_to' => 'simple', // comma-delimited
        'sort_order' =>  100,
    )
);

$this->addAttributeToSet(
    $entityTypeId,  // int
    $this->createAttributeSet('Product'),
    'Other Options',
    'compatibility_product_line',
    100    // sort order
);
