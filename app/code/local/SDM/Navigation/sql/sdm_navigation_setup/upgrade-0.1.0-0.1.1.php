<?php
/**
 * Separation Degrees One
 *
 * Ellison's navigation links
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Navigation
 * @author    Separation Degrees <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$navLinkTabname = 'General Information';

$this->addAttribute(
    Mage_Catalog_Model_Category::ENTITY,
    'open_in_new_tab',
    array(
        'group' => $navLinkTabname,
        'input' => 'select',
        'type' => 'int',
        'source' => 'eav/entity_attribute_source_boolean',
        'label' => 'Open in New Tab?',
        'backend' => '',
        'visible' => true,
        'required' => false,
        'visible_on_front' => false,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'sort_order' => 4,
        'default'  => 0
    )
);
