<?php
/**
 * Separation Degrees Media
 *
 * Ellison's custom product taxonomy implementation.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Taxonomy
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

$this->startSetup();

$this->run("
    ALTER TABLE  `{$this->getTable('taxonomy/item')}`
        ADD COLUMN `image_url` varchar(128) DEFAULT '',
		ADD COLUMN `description` text,
		ADD COLUMN `rich_description` text,
		ADD COLUMN `external_url` varchar(128) DEFAULT '',
		ADD COLUMN `created_at` datetime DEFAULT NULL,
		ADD COLUMN `updated_at` datetime DEFAULT NULL
");

$this->run("
    UPDATE  `{$this->getTable('taxonomy/item')}`
        SET created_at='".Mage::getSingleton('core/date')->gmtDate()."',
        	updated_at='".Mage::getSingleton('core/date')->gmtDate()."'
");

$this->endSetup();
