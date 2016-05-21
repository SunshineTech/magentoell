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
    'type' => 'varchar',
    'label' => 'Print Catalog Download URL',
    'input' => 'text',
    'class' => '',
    'source' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
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
    'used_in_product_listing' => 1,
    'apply_to' => 'simple', // comma-delimited
    'note' => ''
);
$this->addAttribute('catalog_product', 'print_catalog_download_url', $data);
$this->addAttributeToSet('catalog_product', 'Print Catalog', 'General', 'print_catalog_download_url', 999);

$this->updateAttribute('catalog_product', 'print_catalog_download_url', array(
    'used_in_product_listing' => true
));

$this->endSetup();
