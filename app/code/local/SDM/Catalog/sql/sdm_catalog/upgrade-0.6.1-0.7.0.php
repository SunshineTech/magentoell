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

$entityTypeId = Mage::getModel('catalog/product')
    ->getResource()
    ->getEntityType()
    ->getId(); //product entity type

$attributeSet = Mage::getModel('eav/entity_attribute_set')
    ->setEntityTypeId($entityTypeId)
    ->setAttributeSetName("Print Catalog");

$attributeSet->validate();
$attributeSet->save();

$attributeSet->getId();
