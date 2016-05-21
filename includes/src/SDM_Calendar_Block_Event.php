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
 * Event detail block
 */
class SDM_Calendar_Block_Event extends SDM_Calendar_Block_Abstract
{
    /**
     * Get the current event
     *
     * @return SDM_Calendar_Model_Event|boolean
     */
    public function getEvent()
    {
        if (!$this->hasEvent()) {
            if ($this->hasEventId()) {
                $this->setEvent(Mage::getModel('sdm_calendar/event')->load(
                    $this->getEventId()
                ));
            } else {
                $this->setEvent(Mage::registry('current_event'));
            }
        }
        return parent::getEvent();
    }
    /**
     * Get the parent calendar
     *
     * @return SDM_Calendar_Model_Calendar|boolean
     */
    public function getCalendar()
    {
        if (!$this->hasCalendar()) {
            $this->setCalendar(Mage::getModel('sdm_calendar/calendar')->load(
                $this->getEvent()->getCalendarId()
            ));
        }
        return parent::getCalendar();
    }

    /**
     * Get the link back to the calendar
     *
     * @return string
     */
    public function getBackLink()
    {
        return $this->getUrl($this->getCalendar()->getUrl());
    }

    /**
     * Get the name of the link back to the calendar
     *
     * @return string
     */
    public function getBackLabel()
    {
        return $this->__('Back to %s', $this->getCalendar()->getName());
    }

    /**
     * Get the path to the event's image
     *
     * @return string|boolean
     */
    public function getImageUrl()
    {
        $image = $this->getEvent()->getImage();
        if (!$image) {
            return false;
        }
        return Mage::getBaseUrl('media') . $image;
    }
}
