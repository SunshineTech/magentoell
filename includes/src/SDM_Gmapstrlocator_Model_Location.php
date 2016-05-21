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
 * SDM_Gmapstrlocator_Model_Location class
 */
class SDM_Gmapstrlocator_Model_Location extends Mage_Core_Model_Abstract
{
    const IMAGE_FOLDER = 'sdm_gmapstrlocator';

    /**
     * Initialize
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('gmapstrlocator/location');
    }
}
