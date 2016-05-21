<?php
/**
 * Separation Degrees One
 *
 * Ellison's custom product taxonomy implementation.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Taxonomy
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$this->run("
    ALTER TABLE  `{$this->getTable('taxonomy/item')}`
        MODIFY COLUMN `name` VARCHAR(255) DEFAULT NULL COMMENT 'Name',
        MODIFY COLUMN `code` VARCHAR(255) DEFAULT NULL COMMENT 'Code'
");
