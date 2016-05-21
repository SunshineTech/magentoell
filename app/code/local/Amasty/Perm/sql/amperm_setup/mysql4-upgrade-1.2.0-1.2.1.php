<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('admin/user')}` ADD `customer_group_id` INT NOT NULL; 
");

$this->endSetup();