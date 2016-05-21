<?php
/**
 * Separation Degrees Media
 *
 * Extension to upload file
 *
 * PHP Version 5.5
 *
 * @category  SDM
 * @package   SDM_FileUpload
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

$this->startSetup();

$this->run("
    DROP TABLE IF EXISTS `{$this->getTable('sdm_upload/file')}`;
    CREATE TABLE `{$this->getTable('sdm_upload/file')}` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `parent_id` int(10) unsigned NOT NULL,
        `type` varchar(255) DEFAULT '',
        `path` varchar(255) DEFAULT '',
        `filename` varchar(255) NOT NULL DEFAULT '',
        `label` varchar(255) NOT NULL DEFAULT '',
        PRIMARY KEY (`id`),
        UNIQUE KEY `UNQ_KEY_FILEUPLOAD_PARENT_ID_TYPE` (`parent_id`,`type`),
        KEY `IDX_FILEUPLOAD_PARENT_ID` (`parent_id`),
        KEY `IDX_FILEUPLOAD_ID` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->endSetup();
