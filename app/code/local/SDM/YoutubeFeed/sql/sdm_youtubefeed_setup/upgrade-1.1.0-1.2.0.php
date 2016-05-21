<?php
/**
 * Separation Degrees Media
 *
 * Embed Youtube Videos and Playlists
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_YoutubeFeed
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sdm_youtubefeed/video'),
        'designer',
        array(
            'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
            'comment' => 'Designer'
        )
    );

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sdm_youtubefeed/video'),
        'file_url',
        array(
            'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'  => 255,
            'comment' => 'PDF URL'
        )
    );

$installer->endSetup();
