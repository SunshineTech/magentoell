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
        $installer->getTable('sdm_calendar/event'),
        'image',
        array(
            'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'  => 255,
            'comment' => 'Event Image'
        )
    );

$installer->endSetup();
