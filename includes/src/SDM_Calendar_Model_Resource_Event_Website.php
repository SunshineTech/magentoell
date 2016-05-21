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
 * Event/website resource collection model
 */
class SDM_Calendar_Model_Resource_Event_Website
     extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sdm_calendar/event_website', null);
    }

    /**
     * Update event/website association
     *
     * @param SDM_Calendar_Model_Event|int $event
     * @param array                        $websites
     *
     * @return SDM_Calendar_Model_Resource_Event_Website
     */
    public function updateWebsites($event, $websites)
    {
        if (!is_array($websites)) {
            return $this;
        }
        if (is_numeric($event)) {
            $event = Mage::getModel('sdm_calendar/event')->load($event);
        }
        if (!$event || !$event->getId()) {
            return $this;
        }
        $write = $this->_getWriteAdapter();
        $stmt = $write->prepare('DELETE FROM ' . $this->getMainTable()
            . ' WHERE event_id = ?');
        $stmt->execute(array($event->getId()));
        foreach ($websites as $websiteId) {
            $stmt = $write->prepare('INSERT INTO ' . $this->getMainTable()
                . ' (event_id, website_id) VALUES (?, ?)');
            $stmt->execute(array($event->getId(), $websiteId));
        }
        return $this;
    }
}
