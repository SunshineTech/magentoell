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
 * Create calendar table
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sdm_calendar/calendar'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
        ), 'Calendar ID')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name');
$installer->getConnection()->createTable($table);

/**
 * Create event table
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sdm_calendar/event'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
        ), 'Event ID')
    ->addColumn('calendar_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        ), 'Calendar ID')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name')
    ->addColumn('start', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Start Time')
    ->addColumn('end', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'End Time')
    ->addColumn('desc', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        ), 'Description')
    ->addColumn('color', Varien_Db_Ddl_Table::TYPE_TEXT, 6, array(
        ), 'color')
    ->addColumn('recurring', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default'  => SDM_Calendar_Model_Event::RECURRING_NONE,
        ), 'Recurring Status')
    ->addIndex($installer->getIdxName('sdm_calendar/event', array('calendar_id')),
        array('calendar_id')
    )
    ->addForeignKey(
        $installer->getFkName('sdm_calendar/event', 'calendar_id', 'sdm_calendar/calendar', 'id'),
        'calendar_id', $installer->getTable('sdm_calendar/calendar'), 'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
$installer->getConnection()->createTable($table);

$installer->endSetup();
