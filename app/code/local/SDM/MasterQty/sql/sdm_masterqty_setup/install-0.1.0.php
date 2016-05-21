<?php
/**
 * Separation Degrees One
 *
 * Magento catalog Add Attribute (masterqty)
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_MasterQty
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2016 Separation Degrees One (http://www.separationdegrees.com)
 */

$installer = $this;

$installer->startSetup();
/**
 * Create masterqty attribute
 */
$installer->addAttribute('catalog_product', 'masterqty', array(
    'type' => 'int',
    'input' => 'text',
    'label' => 'Master Quantity',
    'class' => '',
    'required' => true,
    'comparable' => false,
    'searchable' => false,
    'is_configurable' => true,
    'user_defined' => true,
    'visible_on_front' => true,
    'required' => true,
    'unique' => false,
    'visible_on_front' => true,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
));
$installer->addAttributeToSet('catalog_product', 'Product', 'Details', 'masterqty', 300);

$installer->endSetup();
