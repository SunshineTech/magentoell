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
 * Calendar render with list of events
 */
class SDM_Calendar_Block_Calendar extends Mage_Core_Block_Template
{
    /**
     * The HTML template for this block
     *
     * @var string
     */
    protected $_template = 'sdm/calendar/calendar.phtml';

    /**
     * Gets the calendar model based on the blocks calendar_id data
     *
     * @return SDM_Calendar_Model_Calendar|boolean
     */
    public function getCalendar()
    {
        if (!$this->hasCalendar()) {
            $calendar = Mage::registry('current_calendar');
            if (!$calendar) {
                $calendar = Mage::getModel('sdm_calendar/calendar')->load(
                    $this->getCalendarId()
                );
            }
            $this->setCalendar($calendar);
        }
        return parent::getCalendar();
    }

    /**
     * The calendar HTML
     *
     * @return string
     */
    public function getEventHtml()
    {
        return $this->getLayout()
            ->createBlock('sdm_calendar/event_list')
            ->setCalendar($this->getCalendar())
            ->toHtml();
    }
}
