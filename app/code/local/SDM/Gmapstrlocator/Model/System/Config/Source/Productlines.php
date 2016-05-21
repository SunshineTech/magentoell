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
 * SDM_Gmapstrlocator_Model_System_Config_Source_Productlines class
 */
class SDM_Gmapstrlocator_Model_System_Config_Source_Productlines
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            'sizzix'    => 'Sizzix',
            'eclips'    => 'eclips',
            'allstar'   => 'AllStar',
            'prestige'  => 'Prestige',
            'quilting'  => 'Quilting'
        );
    }

    /**
     * Multiselect options
     *
     * @return array
     */
    public function toMultiSelectArray()
    {
        return array(
            'sizzix'    => array('label' => 'Sizzix',   'value' => 'Sizzix'),
            'eclips'    => array('label' => 'Eclips',   'value' => 'Eclips'),
            'allstar'   => array('label' => 'Allstar',  'value' => 'Allstar'),
            'prestige'  => array('label' => 'Prestige', 'value' => 'Prestige'),
            'quilting'  => array('label' => 'Quilting', 'value' => 'Quilting')
        );
    }
}
