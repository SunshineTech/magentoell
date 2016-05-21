<?php
/**
 * Separation Degrees Media
 *
 * Implements the product compatibility functionality.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Compatibility
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */


$this->run(
    "ALTER TABLE `{$this->getTable('compatibility/productline')}`
    CHANGE `image_url` `image_link` VARCHAR(255) DEFAULT NULL COMMENT 'Image Link';"
);

$this->run(
    "ALTER TABLE `{$this->getTable('compatibility/compatibility')}`
    ADD position INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Position';"
);
