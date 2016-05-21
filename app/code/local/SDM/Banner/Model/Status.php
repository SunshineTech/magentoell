<?php
/**
 * Separation Degrees One
 *
 * Banner Ads
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Banner
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */
/**
 * SDM_Banner_Model_Status class
 */
class SDM_Banner_Model_Status extends Varien_Object
{
    const STATUS_ENABLED    = 1;
    const STATUS_DISABLED   = 2;

    /**
     * Options getter
     *
     * @return array
     */
    public static function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED    => Mage::helper('slider')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('slider')->__('Disabled')
        );
    }
}
