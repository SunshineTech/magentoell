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
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

$taxonomyItem = $this->getTable('taxonomy/item');
$taxonomyDate = $this->getTable('taxonomy/item_date');

$this->run("
    DROP TABLE IF EXISTS `$taxonomyDate`;
    CREATE TABLE `$taxonomyDate` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Record ID',
        `taxonomy_id` INT(11) UNSIGNED NOT NULL COMMENT 'Parent ID',
        `website_id` SMALLINT(5) DEFAULT NULL COMMENT 'Website ID',
        `start_date` DATETIME DEFAULT NULL COMMENT 'Start Date',
        `end_date` DATETIME DEFAULT NULL COMMENT 'End Date',
        PRIMARY KEY (`id`),
        KEY `IDX_TAXONOMY_DATE_TAG_ID` (`taxonomy_id`),
        CONSTRAINT `FK_TAXONOMY_DATE_TAG_ID` FOREIGN KEY (`taxonomy_id`) REFERENCES `$taxonomyItem` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
