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

/**
 * Calendar/website resource collection model
 */
class SDM_Calendar_Model_Resource_Calendar_Website
     extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sdm_calendar/calendar_website', null);
    }

    /**
     * Update calendar/website association
     *
     * @param SDM_Calendar_Model_Calendar|int $calendar
     * @param array                           $websites
     *
     * @return SDM_Calendar_Model_Resource_Calendar_Website
     */
    public function updateWebsites($calendar, $websites)
    {
        if (!is_array($websites)) {
            return $this;
        }
        if (is_numeric($calendar)) {
            $calendar = Mage::getModel('sdm_calendar/calendar')->load($calendar);
        }
        if (!$calendar || !$calendar->getId()) {
            return $this;
        }
        $write = $this->_getWriteAdapter();
        $stmt = $write->prepare('DELETE FROM ' . $this->getMainTable()
            . ' WHERE calendar_id = ?');
        $stmt->execute(array($calendar->getId()));
        foreach ($websites as $websiteId) {
            $stmt = $write->prepare('INSERT INTO ' . $this->getMainTable()
                . ' (calendar_id, website_id) VALUES (?, ?)');
            $stmt->execute(array($calendar->getId(), $websiteId));
        }
        return $this;
    }
}
