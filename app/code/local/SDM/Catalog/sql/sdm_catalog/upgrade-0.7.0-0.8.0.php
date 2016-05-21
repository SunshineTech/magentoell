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

$config = new Mage_Core_Model_Config();

$data = array(
    'type' => 'int',
    'label' => 'Allow Checkout',
    'input' => 'select',
    'class' => '',
    'source' => 'eav/entity_attribute_source_boolean',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => '1',
    'searchable' => '0',
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'unique' => false,
    'is_html_allowed_on_front' => true,
    'is_configurable' => false,
    'used_in_product_listing' => '1',
    'apply_to' => 'simple', // comma-delimited
    'note' => 'This value is controlled by the current lifecycle settings and cannot be modified manually.'
);
$this->addAttribute('catalog_product', 'allow_checkout', $data);
$this->addAttributeToSet('catalog_product', 'Product', 'Life Cycle', 'allow_checkout', 1000);

$this->updateAttribute('catalog_product', 'allow_sale', array(
    'attribute_code' => 'allow_cart'
));

$this->endSetup();
