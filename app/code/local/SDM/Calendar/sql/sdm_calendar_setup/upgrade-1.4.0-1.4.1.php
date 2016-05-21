<?php
/**
 * Separation Degrees One
 *
 * Ellison's Teachers' Planning Calendar
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Calendar
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$this->startSetup();

// Re-install calendar store table to be calendar website
$this->run("
    DROP TABLE IF EXISTS `{$this->getTable('sdm_calendar/calendar_store')}`;
    DROP TABLE IF EXISTS `{$this->getTable('sdm_calendar/calendar_website')}`;
    CREATE TABLE `{$this->getTable('sdm_calendar/calendar_website')}` (
        `calendar_id` INT(10) UNSIGNED NOT NULL COMMENT 'Calendar ID',
        `website_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Website ID',
        UNIQUE KEY `IDX_SDM_CALENDAR_CALENDAR_ID_WEB_ID` (`calendar_id`,`website_id`),
        KEY `IDX_SDM_CALENDAR_WEB_ID` (`website_id`),
        CONSTRAINT `FK_SDM_CALENDAR_WEB_CALENDAR_ID_SDM_CALENDAR_ID` FOREIGN KEY (`calendar_id`) REFERENCES `sdm_calendar_calendar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `FK_SDM_CALENDAR_WEB_WEB_ID_CORE_WEB_ID` FOREIGN KEY (`website_id`) REFERENCES `core_website` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='sdm_calendar_calendar_store';
");

// Re-install event store table to be event website
$this->run("
    DROP TABLE IF EXISTS `{$this->getTable('sdm_calendar/event_store')}`;
    DROP TABLE IF EXISTS `{$this->getTable('sdm_calendar/event_website')}`;
    CREATE TABLE `{$this->getTable('sdm_calendar/event_website')}` (
        `event_id` INT(10) UNSIGNED NOT NULL COMMENT 'Event ID',
        `website_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Website ID',
        UNIQUE KEY `IDX_SDM_CALENDAR_EVENT_ID_WEB_ID` (`event_id`,`website_id`),
        KEY `FK_SDM_CALENDAR_EVENT_STORE_STORE_ID_CORE_STORE_STORE_ID` (`website_id`),
        CONSTRAINT `FK_SDM_CALENDAR_EVENT_WEB_EVENT_ID_SDM_CALENDAR_ID` FOREIGN KEY (`event_id`) REFERENCES `sdm_calendar_event` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `FK_SDM_CALENDAR_EVENT_WEB_WEB_ID_CORE_WEB_ID` FOREIGN KEY (`website_id`) REFERENCES `core_website` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='sdm_calendar_event_store';
");

$this->endSetup();
