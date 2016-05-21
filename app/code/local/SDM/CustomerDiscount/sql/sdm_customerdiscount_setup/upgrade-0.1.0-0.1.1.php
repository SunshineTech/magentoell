<?php
/**
 * Separation Degrees One
 *
 * Implements the customer/retailer discount logic for viewing and obtaining
 * discounts and prices, as wel as managing the customer groups.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CustomerDiscount
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$data = array(
    'type' => 'int',
    'label' => 'Minimum Qty for Retailer',
    'input' => 'text',
    'class' => '',
    'source' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => false,
    'required' => false,
    'user_defined' => true,
    'default' => '0',
    'searchable' => '0',
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'unique' => false,
    'is_html_allowed_on_front' => false,
    'is_configurable' => false,
    'apply_to' => 'simple',
    'note' => 'Applies only to retailers'
);
$this->addAttribute('catalog_product', 'min_qty', $data);
$this->addAttributeToSet('catalog_product', 'Product', 'General', 'min_qty', 1000);
