<?php
/**
 * Separation Degrees One
 *
 * Ellison's Mage_Customer customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Customer
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

// Install a new customer EAV attribute
$entityTypeId     = $this->getEntityTypeId('customer');
$attributeSetId   = $this->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $this->getDefaultAttributeGroupId(
    $entityTypeId,
    $attributeSetId
);

// ERUS and EEUS customer attributes
$attributes = array(
    'institution_description' => 'Institution Description'
);

foreach ($attributes as $attribute => $label) {
    $this->addAttribute(
        'customer',
        $attribute,
        array(
            'input' => 'select',
            'type' => 'varchar',
            'source' => 'sdm_customer/attribute_source_institutiondescription',
            'label' => $label,
            'visible' => 1,
            'required' => 0,
            'user_defined' => 1,
        )
    );

    $this->addAttributeToGroup(
        $entityTypeId,
        $attributeSetId,
        $attributeGroupId,
        $attribute,
        1650
    );

    $eavConfig = Mage::getSingleton('eav/config')
        ->getAttribute('customer', $attribute)
        ->setData('used_in_forms', array('adminhtml_customer'))
        ->save();
}
