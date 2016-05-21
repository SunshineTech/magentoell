<?php
/**
 * Separation Degrees One
 *
 * Magento catalog customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Catalog
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$this->startSetup();

$att = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'is_orderable');
$att->setData('frontend_label', array('Is Orderable'));
$att->save();

$data = array(
    'type' => 'int',
    'label' => 'Is Preorderable',
    'input' => 'select',
    'class' => '',
    'source' => 'eav/entity_attribute_source_boolean',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => '',
    'searchable' => '0',
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'unique' => false,
    'is_html_allowed_on_front' => true,
    'is_configurable' => false,
    'apply_to' => 'simple' // comma-delimited
);
$this->addAttribute('catalog_product', 'is_preorderable', $data);
$this->addAttributeToSet('catalog_product', 'Product', 'Life Cycle', 'is_preorderable', 100);

$data = array(
    'type' => 'int',
    'label' => 'Is Backorderable',
    'input' => 'select',
    'class' => '',
    'source' => 'eav/entity_attribute_source_boolean',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => '',
    'searchable' => '0',
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'unique' => false,
    'is_html_allowed_on_front' => true,
    'is_configurable' => false,
    'apply_to' => 'simple' // comma-delimited
);
$this->addAttribute('catalog_product', 'is_backorderable', $data);
$this->addAttributeToSet('catalog_product', 'Product', 'Life Cycle', 'is_backorderable', 200);

$data = array(
    'type' => 'int',
    'label' => 'Allow Preorder',
    'input' => 'select',
    'class' => '',
    'source' => 'eav/entity_attribute_source_boolean',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => '',
    'searchable' => '0',
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'unique' => false,
    'is_html_allowed_on_front' => true,
    'is_configurable' => false,
    'apply_to' => 'simple', // comma-delimited
    'note' => 'This value is controlled by the current lifecycle settings and cannot be modified manually.'
);
$this->addAttribute('catalog_product', 'allow_preorder', $data);
$this->addAttributeToSet('catalog_product', 'Product', 'General', 'allow_preorder', 300);

$data = array(
    'type' => 'int',
    'label' => 'Allow Quote',
    'input' => 'select',
    'class' => '',
    'source' => 'eav/entity_attribute_source_boolean',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => '',
    'searchable' => '0',
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'unique' => false,
    'is_html_allowed_on_front' => true,
    'is_configurable' => false,
    'apply_to' => 'simple', // comma-delimited
    'note' => 'This value is controlled by the current lifecycle settings and cannot be modified manually.'
);
$this->addAttribute('catalog_product', 'allow_quote', $data);
$this->addAttributeToSet('catalog_product', 'Product', 'General', 'allow_quote', 400);

$this->updateAttribute('catalog_product', 'visibility', 'note', 'This value is controlled by the current lifecycle settings and cannot be modified manually.');


$this->endSetup();
