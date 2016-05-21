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

/**
 * Add new navigation link-related attributes
 */
$this->addAttribute(
    Mage_Catalog_Model_Category::ENTITY,
    'filtering_parameter',
    array(
        'group' => $navLinkTabname,
        'input' => 'text',
        'type' => 'varchar',
        'label' => 'Filtering Parameter',
        'backend' => '',
        'visible' => true,
        'required' => false,
        'visible_on_front' => false,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'sort_order' => 3,
    )
);
