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

// ERUS customer min. order amounts
$attributes = array(
    'can_use_purchase_order' => 'Allow Purchase Order Usage',
);

$sortOrder = 1300;  // Last attribute added as 1200
foreach ($attributes as $attribute => $label) {
    $this->addAttribute(
        'customer',
        $attribute,
        array(
            'input' => 'boolean',
            'type' => 'int',
            'label' => $label,
            'visible' => 1,
            'required' => 1,
            'user_defined' => 1,
            'backend' => 'customer/attribute_backend_data_boolean'
        )
    );

    $this->addAttributeToGroup(
        $entityTypeId,
        $attributeSetId,
        $attributeGroupId,
        $attribute,
        $sortOrder
    );

    $eavConfig = Mage::getSingleton('eav/config')
        ->getAttribute('customer', $attribute)
        ->setData('used_in_forms', array('adminhtml_customer'))
        ->save();

    $sortOrder += 100;
}
