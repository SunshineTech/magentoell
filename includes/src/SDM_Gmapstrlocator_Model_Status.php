<?php
/**
 * Separation Degrees Media
 *
 * This module handles all the store locator functionality for Ellison.
 *
 * The original code from this module is based off the FME_Gmapstrlocator module. We converted their
 * module to an SDM module rather than extending from it because the amount of modifications and
 * rewrites necessary for it to fit Ellison's spec were extensive, yet we still felt there was value
 * in using FME's module as a starting point.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Gmapstrlocator
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Gmapstrlocator_Model_Status class
 */
class SDM_Gmapstrlocator_Model_Status extends Varien_Object
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
            self::STATUS_ENABLED    => Mage::helper('gmapstrlocator')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('gmapstrlocator')->__('Disabled')
        );
    }
}
