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
 * System config array of event colors
 */
class SDM_Calendar_Model_Adminhtml_System_Config_Source_Event_Color
{
    /**
     * Options getter
     *
     * @param  boolean $asValueLabel
     * @return array
     */
    public function toOptionArray($asValueLabel = true)
    {
        $colors = array();
        $config = Mage::getStoreConfig(SDM_Calendar_Helper_Data::XML_PATH_EVENT_COLOR);
        foreach ($config as $color) {
            $colors[$color['color']] = $color['color'];
        }
        if ($asValueLabel) {
            $newColors = array();
            foreach ($colors as $value => $label) {
                $newColors[] = array(
                    'value' => $value,
                    'label' => $label
                );
            }
            return $newColors;
        }
        return $colors;
    }
}
