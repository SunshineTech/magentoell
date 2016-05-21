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
 * Renders events this month
 */
class SDM_Calendar_Block_Event_List extends SDM_Calendar_Block_Abstract
{
    const MONTH_CUTOFF = 1;

    /**
     * Set the HTML template for this block
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $calendar = $this->getCalendar() ?: Mage::registry('current_calendar');
        $this->setTemplate('sdm/calendar/event/' . $calendar->getType() . '.phtml');
    }

    /**
     * Array of all events to be shown
     *
     * @param boolean $asJson
     *
     * @return array
     */
    public function getEvents($asJson = false)
    {
        $events = $this->getNonRecurringEvents($asJson);
        $events = array_merge($events, $this->getYearlyRecurringEvents());
        if ($asJson) {
            return Mage::helper('core')->jsonEncode($events);
        }
        return $events;
    }

    /**
     * Prepare all non-recurring events
     *
     * @param boolean $cutoff
     *
     * @return array
     */
    public function getNonRecurringEvents($cutoff = true)
    {
        $events = array();
        $collection = Mage::getModel('sdm_calendar/event')->getCollection()
            ->addFieldToFilter('calendar_id', $this->getCalendar()->getId())
            ->addFieldToFilter('recurring', SDM_Calendar_Model_Event::RECURRING_NONE)
            ->setOrder('start', Zend_Db_Select::SQL_ASC);
        if ($this->getCalendar()->getType() == 'list') {
            $collection->addFieldToFilter('end', array('gteq' => $this->getViewedDate()));
        }
        $collection->join(
            array('event_website' => 'sdm_calendar/event_website'),
            'main_table.id = event_website.event_id AND event_website.website_id = '
                . Mage::app()->getWebsite()->getId(),
            false
        );
        if ($cutoff) {
            $collection->getSelect()
                ->joinInner(
                    array('taxonomy_item' => $collection->getTable('taxonomy/item')),
                    'main_table.taxonomy_id = taxonomy_item.entity_id',
                    array('taxonomy_type' => 'taxonomy_item.type')
                )
                ->where('start >= "' . $this->getViewedDate() . '" - INTERVAL ' . self::MONTH_CUTOFF . ' MONTH')
                ->where('end <= "' . $this->getViewedDate() . '" + INTERVAL ' . self::MONTH_CUTOFF . ' MONTH');
        }
        foreach ($collection as $model) {
            $events[] = $this->prepareEvent($model);
        }
        return $events;
    }

    /**
     * Prepare all yearly recurring events
     *
     * @param boolean $cutoff
     *
     * @return array
     */
    public function getYearlyRecurringEvents($cutoff = true)
    {
        $events = array();
        $collection = Mage::getModel('sdm_calendar/event')->getCollection()
            ->addFieldToFilter('calendar_id', $this->getCalendar()->getId())
            ->addFieldToFilter('recurring', SDM_Calendar_Model_Event::RECURRING_YEARLY);
        $collection->join(
            array('event_website' => 'sdm_calendar/event_website'),
            'main_table.id = event_website.event_id AND event_website.website_id = '
                . Mage::app()->getWebsite()->getId(),
            false
        );
        if ($cutoff) {
            $collection->getSelect()
                ->joinInner(
                    array('taxonomy_item' => $collection->getTable('taxonomy/item')),
                    'main_table.taxonomy_id = taxonomy_item.entity_id',
                    array('taxonomy_type' => 'taxonomy_item.type')
                )
                ->where('start <= "' . $this->getViewedDate() . '" + INTERVAL ' . self::MONTH_CUTOFF . ' MONTH');
        }
        foreach ($collection as $model) {
            $events[] = $this->prepareYearlyEvent($model);
        }
        return $events;
    }

    /**
     * Builds event data from select into json array format
     *
     * @param SDM_Calendar_Model_Event $model
     *
     * @return array
     */
    public function prepareEvent(SDM_Calendar_Model_Event $model)
    {
        $start = $model->getStart();
        $end   = $model->getEnd();
        $event = array(
            'id'       => $model->getId(),
            'title'    => $model->getName(),
            'image'    => $model->getImage(),
            'location' => $model->getLocation(),
            'street'   => $model->getStreet(),
            'city'     => $model->getCity(),
            'state'    => $model->getState(),
            'zip'      => $model->getZip(),
            'country'  => $model->getCountry(),
            'start'    => $this->getDateSingleton()->date('Y-m-d', $this->getDateSingleton()->gmtTimestamp($start)),
            'url'      => $this->getUrl('catalog') . '?' . http_build_query(array(
                'tag_' . $model->getTaxonomyType() => $model->getTaxonomyId(),
                'type'                             => 'project'
            )),
            'color'   => '#' . ($model->getColor() ?: 'ffffff'),
        );
        if ($start != $end) {
            $event['end'] = $this->getDateSingleton()->date('Y-m-d', $this->getDateSingleton()->gmtTimestamp($end));
        }
        return $event;
    }

    /**
     * Yearly recurring events need a little more work
     *
     * Here be dragons
     *
     * Use shell/sdm/calendar.php to test
     *
     * @param SDM_Calendar_Model_Event $model
     *
     * @return array
     */
    public function prepareYearlyEvent(SDM_Calendar_Model_Event $model)
    {
        $thisYear        = $this->getDateSingleton()->gmtDate('Y', $this->getToday());
        $eventStartYear  = $this->getDateSingleton()->gmtDate('Y', $model->getStart());
        $thisMonth       = $this->getDateSingleton()->gmtDate('m', $this->getToday());
        $eventStartMonth = $this->getDateSingleton()->gmtDate('m', $model->getStart());
        $eventStartDay   = $this->getDateSingleton()->gmtDate('d', $model->getStart());
        if ($eventStartMonth < $thisMonth) {
            if ($thisMonth - $eventStartMonth <= self::MONTH_CUTOFF) {
                $newYear = $thisYear;
            } else {
                $newYear = $thisYear + 1;
            }
        } elseif ($eventStartMonth > $thisMonth) {
            if ($eventStartMonth - $thisMonth <= self::MONTH_CUTOFF) {
                $newYear = $thisYear;
            } else {
                $newYear = $thisYear - 1;
            }
        } else {
            $newYear = $thisYear;
        }
        if ($newYear != $eventStartYear) {
            $model->setStart($this->_formatDate($newYear, $eventStartMonth, $eventStartDay));
            if ($model->getEnd()) {
                $yearDiff = $newYear - $eventStartYear;
                $model->setEnd(
                    $this->_formatDate(
                        $this->getDateSingleton()->gmtDate('Y', $model->getEnd()) + $yearDiff,
                        $this->getDateSingleton()->gmtDate('m', $model->getEnd()),
                        $this->getDateSingleton()->gmtDate('d', $model->getEnd())
                    )
                );
            }
        }
        $event = $this->prepareEvent($model);
        return $event;
    }

    /**
     * Format year, month, and day to a string
     *
     * @param integer $year
     * @param integer $month
     * @param integer $day
     *
     * @return string
     */
    protected function _formatDate($year, $month, $day)
    {
        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    /**
     * Get the date for the view we are currently looking at
     *
     * @return string
     */
    protected function getViewedDate()
    {
        if (!$this->hasViewedDate()) {
            $this->setViewedDate(Mage::getSingleton('core/date')->date(
                $this->getRequest()->getParam('year') . '-' . $this->getRequest()->getParam('month') . '-d'
            ));
        }
        return parent::getViewedDate();
    }

    /**
     * Get's todays date
     *
     * @return string
     */
    public function getToday()
    {
        if (!$this->hasToday()) {
            $this->setToday($this->getDateSingleton()->gmtDate('Y-m-d'));
        }
        return parent::getToday();
    }

    /**
     * Return the year
     *
     * @return string
     */
    public function getYear()
    {
        return $this->getRequest()->getParam('year');
    }

    /**
     * Return the year
     *
     * @return string
     */
    public function getIntegerMonth()
    {
        return $this->getRequest()->getParam('month');
    }

    /**
     * Return the year
     *
     * @return string
     */
    public function getMonth()
    {
        return $this->_padLeadingZero($this->getIntegerMonth());
    }

    /**
     * Return the year
     *
     * @return string
     */
    public function getPreviousMonth()
    {
        $month = $this->getIntegerMonth();
        if ($month == 1) {
            return '12';
        }

        return $this->_padLeadingZero($month - 1);
    }

    /**
     * Return the year
     *
     * @return string
     */
    public function getNextMonth()
    {
        $month = $this->getIntegerMonth();
        if ($month == 12) {
            return '01';
        }

        return $this->_padLeadingZero($month + 1);
    }

    /**
     * Adds a leading '0' to the month number if $month < 10, as it's required
     * for FullCalendar.
     *
     * @param string $month
     *
     * @return string
     */
    protected function _padLeadingZero($month)
    {
        return str_pad($month, 2, '0', STR_PAD_LEFT);
    }
}
