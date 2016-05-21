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

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sdm_calendar/calendar'),
        'type',
        array(
            'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'  => 255,
            'comment' => 'Calendar Type',
            'default' => SDM_Calendar_Model_Calendar::TYPE_GRID
        )
    );

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sdm_calendar/calendar'),
        'desc',
        array(
            'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
            'comment' => 'Calendar Description',
        )
    );

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sdm_calendar/event'),
        'location',
        array(
            'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'  => 255,
            'comment' => 'Location Name'
        )
    );

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sdm_calendar/event'),
        'street',
        array(
            'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'  => 255,
            'comment' => 'Event Street'
        )
    );

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sdm_calendar/event'),
        'city',
        array(
            'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'  => 255,
            'comment' => 'Event City'
        )
    );

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sdm_calendar/event'),
        'state',
        array(
            'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'  => 255,
            'comment' => 'Event State'
        )
    );

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sdm_calendar/event'),
        'zip',
        array(
            'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'  => 255,
            'comment' => 'Event Zip Code'
        )
    );

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sdm_calendar/event'),
        'country',
        array(
            'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'  => 255,
            'comment' => 'Event Country'
        )
    );

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sdm_calendar/event'),
        'sidebar',
        array(
            'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
            'comment' => 'Event Sidebar Content'
        )
    );

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sdm_calendar/event'),
        'taxonomy_id',
        array(
            'type'    => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'comment' => 'Event Taxonomy Id'
        )
    );

$installer->getConnection()
    ->addIndex(
        $installer->getTable('sdm_calendar/event'),
        $installer->getIdxName('sdm_calendar/event', array('taxonomy_id')),
        array('taxonomy_id')
    );

$installer->getConnection()
    ->addForeignKey(
        $installer->getFkName('sdm_calendar/event', 'calendar_id', 'taxonomy/item', 'entity_id'),
        $installer->getTable('sdm_calendar/event'),
        'taxonomy_id',
        $installer->getTable('taxonomy/item'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$eavInstaller = new Mage_Eav_Model_Entity_Setup('core_setup');

$eavInstaller->addAttribute('catalog_product', 'tag_event', array(
    'type'              => 'varchar',
    'backend'           => 'eav/entity_attribute_backend_array',
    'label'             => 'Event',
    'input'             => 'multiselect',
    'source'            => 'taxonomy/attribute_source_event',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'searchable'        => false,
    'filterable'        => true,
    'comparable'        => false,
    'visible_on_front'  => false,
    'group'             => 'Taxonomy'
));

$installer->endSetup();
