<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */
$this->startSetup();

$this->run("
	ALTER TABLE `{$this->getTable('amperm/message')}` ADD `author_id` int(10) unsigned NOT NULL default '0';
"); 

$this->endSetup();