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
 * Calendar resource collection model
 */
class SDM_Calendar_Model_Resource_Calendar_Collection
     extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sdm_calendar/calendar');
    }

    /**
     * Convert collection items to select options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->_toOptionArray('id', 'name');
        $sort = array();
        foreach ($options as $data) {
            $name = $data['label'];
            if (!empty($name)) {
                $sort[$name . ' [' . $data['value'] . ']'] = $data['value'];
            }
        }
        Mage::helper('core/string')->ksortMultibyte($sort);
        return array_flip($sort);
    }
}
