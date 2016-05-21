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
 * System config array of calendars
 */
class SDM_Calendar_Model_Adminhtml_System_Config_Source_Calendar
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $valuesLabels = array();
        $options = Mage::getResourceModel('sdm_calendar/calendar_collection')
            ->loadData()
            ->toOptionArray();
        foreach ($options as $value => $label) {
            $valuesLabels[] = array(
                'value' => $value,
                'label' => $label
            );
        }
        return $valuesLabels;
    }
}
