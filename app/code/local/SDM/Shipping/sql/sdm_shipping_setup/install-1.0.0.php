<?php
/**
 * Separation Degrees One
 *
 * Ellison and Sizzix Shipping Rules
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Shipping
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/* @var $installer Mage_Sales_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$entitiesToAlter = array(
    'quote_item',
    'quote_address',
    'order_address'
);

$attributes = array(
    'sdm_shipping_surcharge'      => array('type' => Varien_Db_Ddl_Table::TYPE_DECIMAL),
    'base_sdm_shipping_surcharge' => array('type' => Varien_Db_Ddl_Table::TYPE_DECIMAL)
);

foreach ($entitiesToAlter as $entityName) {
    foreach ($attributes as $attributeCode => $attributeParams) {
        $installer->addAttribute($entityName, $attributeCode, $attributeParams);
    }
}

$table = $installer->getConnection()
    ->newTable($installer->getTable('sdm_shipping/rate_eu'))
    ->addColumn('min', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'primary'  => true,
        ), 'Minimum Total')
    ->addColumn('max', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'primary'  => true,
        ), 'Maximum Total')
    ->addColumn('country_id', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
        'primary'  => true,
        ), 'Country ID')
    ->addColumn('rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'primary'  => true,
        ), 'Shipping Rate')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'primary'  => true,
        ), 'Store ID')
    ->addIndex($installer->getIdxName('sdm_shipping/rate_eu', array('store_id')),
        array('store_id')
    )
    ->addForeignKey($installer->getFkName('sdm_shipping/rate_eu', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
$installer->getConnection()->createTable($table);

$installer->endSetup();
