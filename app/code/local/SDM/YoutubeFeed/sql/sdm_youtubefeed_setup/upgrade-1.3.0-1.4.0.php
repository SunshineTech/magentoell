<?php
/**
 * Master Software Solutions
 *
 * Embed Youtube Videos and Playlists
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_YoutubeFeed
 * @author    Master Software Solutions
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sdm_youtubefeed/playlist'),
        'websites',
        array(
            'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'  => 20,
            'comment' => 'Playlist Website'
        )
    );

$installer->endSetup();

