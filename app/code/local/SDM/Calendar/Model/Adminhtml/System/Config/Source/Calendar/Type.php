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
 * System config array of calendar types
 */
class SDM_Calendar_Model_Adminhtml_System_Config_Source_Calendar_Type
{
    /**
     * Options getter
     *
     * @param  boolean $asValueLabel
     * @return array
     */
    public function toOptionArray($asValueLabel = true)
    {
        $options = array(
            SDM_Calendar_Model_Calendar::TYPE_GRID => Mage::helper('sdm_calendar')->__('Grid'),
            SDM_Calendar_Model_Calendar::TYPE_LIST => Mage::helper('sdm_calendar')->__('List'),
        );
        if ($asValueLabel) {
            $newOptions = array();
            foreach ($options as $value => $label) {
                $newOptions[] = array(
                    'value' => $value,
                    'label' => $label,
                );
            }
            return $newOptions;
        }
        return $options;
    }
}
