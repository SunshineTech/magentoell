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
class SDM_Calendar_Model_Resource_Calendar_Website_Collection
     extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sdm_calendar/calendar_website');
    }
}
