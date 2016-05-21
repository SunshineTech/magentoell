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
class SDM_Calendar_Model_Adminhtml_System_Config_Source_Website
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $values = array();
        $websites = Mage::getModel('core/website')->getCollection();
        foreach ($websites as $website) {
            $values[] = array(
                'value'    => $website->getId(),
                'label'    => $website->getName() . ' (' . $website->getCode() . ')',
            );
        }
        return $values;
    }
}
