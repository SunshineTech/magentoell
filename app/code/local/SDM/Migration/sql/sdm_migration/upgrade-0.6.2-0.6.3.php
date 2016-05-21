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

$attributeId = $this->addAttribute(
    $entityTypeId,
    'tag_discount_category',
    array(
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => 'Discount Category',
        'input' => 'select',
        'class' => '',
        'source' => 'taxonomy/attribute_source_discountcategory',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => true,
        'required' => true,
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
        'apply_to' => 'simple', // comma-delimited
        'sort_order' =>  130,
    )
);

$this->addAttributeToSet(
    $entityTypeId,  // int
    $this->createAttributeSet('Product'),
    'Other Options',
    'tag_discount_category',
    130    // sort order
);
