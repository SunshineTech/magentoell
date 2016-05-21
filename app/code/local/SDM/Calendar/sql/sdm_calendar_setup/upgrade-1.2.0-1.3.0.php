<?php
/**
 * Separation Degrees One
 *
 * Ellison's Teachers' Planning Calendar
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Calendar
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Create calendar/store table
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sdm_calendar/calendar_store'))
    ->addColumn('calendar_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        ), 'Calendar ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        ), 'Store ID')
    ->addIndex($installer->getIdxName('sdm_calendar/calendar_store', array('calendar_id', 'store_id'), true),
        array('calendar_id', 'store_id'), array('type' => 'unique')
    )
    ->addForeignKey($installer->getFkName('sdm_calendar/calendar_store', 'calendar_id', 'sdm_calendar/calendar', 'id'),
        'calendar_id', $installer->getTable('sdm_calendar/calendar'), 'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('sdm_calendar/calendar_store', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
$installer->getConnection()->createTable($table);

/**
 * Create event/store table
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sdm_calendar/event_store'))
    ->addColumn('event_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        ), 'Event ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        ), 'Store ID')
    ->addIndex($installer->getIdxName('sdm_calendar/event_store', array('event_id', 'store_id'), true),
        array('event_id', 'store_id'), array('type' => 'unique')
    )
    ->addForeignKey($installer->getFkName('sdm_calendar/event_store', 'event_id', 'sdm_calendar/event', 'id'),
        'event_id', $installer->getTable('sdm_calendar/event'), 'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('sdm_calendar/event_store', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
$installer->getConnection()->createTable($table);

$installer->endSetup();
