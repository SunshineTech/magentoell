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
    ALTER TABLE  `{$this->getTable('auguria_sliders/sliders')}`
        ADD COLUMN `image_mobile` varchar(255) DEFAULT '' AFTER `image`
");

$this->endSetup();
