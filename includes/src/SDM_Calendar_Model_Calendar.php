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
 * Calendar model
 */
class SDM_Calendar_Model_Calendar extends Mage_Core_Model_Abstract
{
    const TYPE_GRID = 'grid';
    const TYPE_LIST = 'list';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'sdm_calendar_calendar';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'calendar';

    /**
     * Initialize model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sdm_calendar/calendar');
    }

    /**
     * Processing object after load data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterLoad()
    {
        $websiteIds = array();
        $collection = Mage::getResourceModel('sdm_calendar/calendar_website_collection');
        $collection->addFieldToFilter('calendar_id', $this->getId());
        foreach ($collection as $calendarWebsite) {
            $websiteIds[] = $calendarWebsite->getWebsiteId();
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
        Mage::getResourceModel('sdm_calendar/calendar_website')
            ->updateWebsites($this, $this->getWebsites());
        return parent::_afterSave();
    }
}
