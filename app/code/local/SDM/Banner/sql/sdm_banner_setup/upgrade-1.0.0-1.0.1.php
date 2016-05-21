<?php
/**
 * Separation Degrees One
 *
 * Banner Ads
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Banner
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */
$this->startSetup();
$this->run("
    ALTER TABLE  `{$this->getTable('slider/slider')}`
        ADD COLUMN `mobileimage` varchar(255) NOT NULL DEFAULT ''
");

$this->endSetup();
