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
 * Event model
 */
class SDM_Calendar_Model_Event extends Mage_Core_Model_Abstract
{
    const RECURRING_NONE   = 0;
    const RECURRING_YEARLY = 1;

    const IMAGE_FOLDER = 'sdm_calendar';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'sdm_calendar_event';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'event';

    /**
     * Initialize model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sdm_calendar/event');
    }

    /**
     * Get the calendar that owns this event
     *
     * @return SDM_Calendar_Model_Calendar|boolean
     */
    public function getCalendar()
    {
        if (!$this->hasCalendar()) {
            $this->setCalendar(
                Mage::getModel('sdm_calendar/calendar')
                    ->load($this->getCalendarId())
            );
        }
        return parent::getCalendar();
    }

    /**
     * Processing object after load data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterLoad()
    {
        $websiteIds = array();
        $collection = Mage::getResourceModel('sdm_calendar/event_website_collection');
        $collection->addFieldToFilter('event_id', $this->getId());
        foreach ($collection as $website) {
            $websiteIds[] = $website->getWebsiteId();
        }
        $this->setWebsites($websiteIds);
        return parent::_afterLoad();
    }

    /**
     * Processing object after save data
     *
     * @return SDM_Calendar_Model_Calendar
     */
    protected function _afterSave()
    {
        Mage::getResourceModel('sdm_calendar/event_website')
            ->updateWebsites($this, $this->getWebsites());
        return parent::_afterSave();
    }
}
