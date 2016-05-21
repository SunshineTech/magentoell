<?php
/**
 * Separation Degrees Media
 *
 * Added Store Image
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Gmapstrlocator
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn(
        $installer->getTable('gmapstrlocator/gmapstrlocator_location'),
        'image',
        array(
            'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'  => 255,
            'comment' => 'Store Image'
        )
    );

$installer->endSetup();
