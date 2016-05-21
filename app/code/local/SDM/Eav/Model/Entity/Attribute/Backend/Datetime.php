<?php
/**
 * Separation Degrees One
 *
 * Modifications to Magento's EAV Attributes
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Eav
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Eav_Model_Entity_Attribute_Backend_Datetime
 */
class SDM_Eav_Model_Entity_Attribute_Backend_Datetime
    extends Mage_Eav_Model_Entity_Attribute_Backend_Datetime
{
    /**
     * Modification to properly save display_start_date and display_end_date so
     * that the time portion is not truncated off (and subsequently reset to 12AM)
     *
     * @param  string|int $date
     * @return string
     */
    public function formatDate($date)
    {
        $code = $this->getAttribute()->getAttributeCode();
        if ($code === 'display_start_date' || $code === 'display_end_date') {
            return empty($date) ? null : date('Y-m-d H:i:s', strtotime($date));
        }
        return parent::formatDate($date);
    }
}
