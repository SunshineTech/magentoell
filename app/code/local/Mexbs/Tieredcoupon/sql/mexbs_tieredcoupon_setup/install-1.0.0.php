<?php
/**
 * DB update script for creating the grouping coupon table
 *
 * @copyright MexBS
 * @author MexBS <it@mexbs.com>
 */

$installer = $this;
/**
 * @var $installer Mage_Core_Model_Resource_Setup
 */

$installer->startSetup();
/**
 * Create table 'mexbs_tieredcoupon/tieredcoupon'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('mexbs_tieredcoupon/tieredcoupon'))
    ->addColumn('tieredcoupon_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Tiered Coupon Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    ), 'Name')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    ), 'Code')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
    ), 'Description')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
    ), 'Is Active')
    ->addIndex($installer->getIdxName('mexbs_tieredcoupon/tieredcoupon', array('code')),
        array('code'))
    ->setComment('Tiered Coupon');

$installer->getConnection()->createTable($table);

/**
 * Create table 'mexbs_tieredcoupon/tieredcoupon_coupon'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('mexbs_tieredcoupon/tieredcoupon_coupon'))
    ->addColumn('tieredcoupon_coupon_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Groping Coupon to Coupon ID')
    ->addColumn('tieredcoupon_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Groping Coupon ID')
    ->addColumn('coupon_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Coupon ID')
    ->addIndex($installer->getIdxName('mexbs_tieredcoupon/tieredcoupon_coupon', array('tieredcoupon_id')),
        array('tieredcoupon_id'))
    ->addForeignKey($installer->getFkName('mexbs_tieredcoupon/tieredcoupon_coupon', 'tieredcoupon_id', 'mexbs_tieredcoupon/tieredcoupon', 'tieredcoupon_id'),
        'tieredcoupon_id', $installer->getTable('mexbs_tieredcoupon/tieredcoupon'), 'tieredcoupon_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('mexbs_tieredcoupon/tieredcoupon_coupon', 'coupon_id', 'salesrule/coupon', 'coupon_id'),
        'coupon_id', $installer->getTable('salesrule/coupon'), 'coupon_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Tiered Coupon to Coupon');

$installer->getConnection()->createTable($table);

$this->endSetup();