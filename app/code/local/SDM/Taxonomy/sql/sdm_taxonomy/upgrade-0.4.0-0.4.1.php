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
    ALTER TABLE `{$this->getTable('taxonomy/item')}` MODIFY COLUMN image_url VARCHAR(255) DEFAULT NULL;
    ALTER TABLE `{$this->getTable('taxonomy/item')}` MODIFY COLUMN description TEXT DEFAULT NULL;
    ALTER TABLE `{$this->getTable('taxonomy/item')}` MODIFY COLUMN rich_description TEXT DEFAULT NULL;
    ALTER TABLE `{$this->getTable('taxonomy/item')}` MODIFY COLUMN external_url VARCHAR(255) DEFAULT NULL;
");
