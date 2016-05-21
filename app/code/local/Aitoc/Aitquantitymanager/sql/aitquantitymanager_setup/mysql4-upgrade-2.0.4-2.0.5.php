<?php
/**
 * Multi-Location Inventory
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitquantitymanager
 * @version      2.2.3
 * @license:     rJhV4acfvLy4sPgpe7MoLJnfOEhDVfWVuKRvbpcv30
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
/**
 * @copyright  Copyright (c) 2011 AITOC, Inc.
 */

/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

$entityTypeId = 'catalog_product';
$preorderId = $this->getAttribute($entityTypeId, 'preorder', 'attribute_id');
$preorderscriptId = $this->getAttribute($entityTypeId, 'preorderdescript', 'attribute_id');

$aitquantitymanagerTable = null;

try {
	$aitquantitymanagerTable = $installer->getTable('aitquantitymanager/stock_item');
}
catch (Exception $e) {}

if (!$aitquantitymanagerTable)
{
	 $aitquantitymanagerTable = ($tablePrefix = (string) Mage::getConfig()->getTablePrefix() ? $tablePrefix : '') . 'aitoc_cataloginventory_stock_item';
}

if ($aitquantitymanagerTable && $installer->tableExists($aitquantitymanagerTable))
{
	$updatePreorder = true;
}

$attributeIds = array();

if ($preorderId)
{
	$attributeIds[] = $preorderId;
}

if ($preorderscriptId)
{
	$attributeIds[] = $preorderscriptId;
}

if ($updatePreorder && count($attributeIds))
{
	$installer->run('
		UPDATE
			' . $installer->getTable('catalog/eav_attribute') . '
		SET
			is_global = ' . Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE . '
		WHERE
			attribute_id IN (' . implode(',', $attributeIds) . ')');
}

$installer->endSetup();