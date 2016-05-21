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
 * SDM_Gmapstrlocator_Model_System_Config_Source_Storetypes class
 */
class SDM_Gmapstrlocator_Model_System_Config_Source_Storetypes
{
    /**
     * Options getter
     *
     * @return array
     */
    public static function toOptionArray()
    {
        return array(
            'physical'  => "Brick and Mortar",
            'online'    => "Online Store",
            'catalog'   => "Catalog Company"
        );
    }

    /**
     * Multiselect options
     *
     * @return array
     */
    public static function toMultiSelectArray()
    {
        return array(
            'physical'    => array('label' => 'Brick and Mortar',   'value' => 'physical'),
            'online'      => array('label' => 'Online Store',       'value' => 'online'),
            'catalog'     => array('label' => 'Catalog Company',    'value' => 'catalog')
        );
    }
}
