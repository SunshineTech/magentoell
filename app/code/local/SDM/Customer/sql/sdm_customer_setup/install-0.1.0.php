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

$this->addAttribute(
    'customer',
    'ax_customer_id',
    array(
        'input' => 'text',
        'type' => 'varchar',
        'label' => 'AX Customer Account ID',
        'visible' => 1,
        'required' => 0,
        'user_defined' => 1,
    )
);

$this->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'ax_customer_id',
    '999'  //sort_order
);

$oAttribute = Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'ax_customer_id');
$oAttribute->setData('used_in_forms', array('adminhtml_customer'));
$oAttribute->save();
