<?php
/**
 * Separation Degrees One
 *
 * Manages the retailer application
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Customer
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */
$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');

$this->startSetup();

// is_editable is missing the attribute entry to function properly
$installer->addAttribute(
    2,  // address entity
    'is_editable',
    array(
        'label' => 'Is Editable',
        'type'  => 'static'
    )
);

$this->endSetup();
