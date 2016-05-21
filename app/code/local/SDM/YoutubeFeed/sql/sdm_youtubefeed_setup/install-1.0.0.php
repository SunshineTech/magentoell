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

/**
 * Create channel table
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sdm_youtubefeed/channel'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
        ), 'Channel ID')
    ->addColumn('identifier', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => false,
        ), 'Identifier')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name');
$installer->getConnection()->createTable($table);

/**
 * Create playlist table
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sdm_youtubefeed/playlist'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
        ), 'Playlist ID')
    ->addColumn('identifier', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => false,
        ), 'Identifier')
    ->addColumn('channel_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        ), 'Channel ID')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name')
    ->addIndex($installer->getIdxName('sdm_youtubefeed/playlist', array('channel_id')),
        array('channel_id')
    )
    ->addForeignKey($installer->getFkName('sdm_youtubefeed/playlist', 'channel_id', 'sdm_youtubefeed/channel', 'id'),
        'channel_id', $installer->getTable('sdm_youtubefeed/channel'), 'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
$installer->getConnection()->createTable($table);

/**
 * Create video table
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sdm_youtubefeed/video'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
        ), 'Video ID')
    ->addColumn('identifier', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => false,
        ), 'Identifier')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'description')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => false,
        'default'  => '0',
        ), 'Status')
    ->addColumn('featured', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => false,
        'default'  => '0',
        ), 'Featured')
    ->addColumn('published_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Published at')
    ->addColumn('duration', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Duration in seconds')
    ->addColumn('views', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Views');
$installer->getConnection()->createTable($table);

/**
 * Create playlist/video relation table
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sdm_youtubefeed/playlist_video'))
    ->addColumn('playlist_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        ), 'Playlist ID')
    ->addColumn('video_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        ), 'Video ID')
    ->addIndex($installer->getIdxName('sdm_youtubefeed/playlist_video', array('playlist_id', 'video_id'), true),
        array('playlist_id', 'video_id'), array('type' => 'unique')
    )
    ->addForeignKey($installer->getFkName('sdm_youtubefeed/playlist_video', 'playlist_id', 'sdm_youtubefeed/playlist', 'id'),
        'playlist_id', $installer->getTable('sdm_youtubefeed/playlist'), 'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('sdm_youtubefeed/playlist_video', 'video_id', 'sdm_youtubefeed/video', 'id'),
        'video_id', $installer->getTable('sdm_youtubefeed/video'), 'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
$installer->getConnection()->createTable($table);

$installer->endSetup();
