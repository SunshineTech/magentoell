<?php
/**
 * Separation Degrees Media
 *
 * Ellison's custom Landing Page Management System (LPMS).
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Lpms
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

$this->startSetup();

$this->run("
    ALTER TABLE  `{$this->getTable('cms/page')}`
		ADD COLUMN `type` varchar(24) DEFAULT NULL,
		ADD COLUMN `taxonomy_id` int(11) unsigned DEFAULT NULL,
		ADD COLUMN `publish_time` datetime DEFAULT NULL,
		ADD COLUMN `publish_author` varchar(48) DEFAULT NULL
");

$this->run("
    UPDATE  `{$this->getTable('cms/page')}`
        SET type='page'
");

$this->endSetup();
