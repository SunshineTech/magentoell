<?php
/**
 * Separation Degrees One
 *
 * Press release listing and article rendering
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_PressReleases
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_PressReleases_Block_Filter class
 */
class SDM_PressReleases_Block_Filter extends Mage_Core_Block_Template
{
    /**
     * Get filters
     *
     * @return mixed
     */
    public function getFilters()
    {
        return Mage::helper('sdm_pressreleases')->getPressReleaseDates();
    }
    /**
     * Get currnt filter
     *
     * @return mixed
     */
    public function getCurrentFilter()
    {
        return Mage::helper('sdm_pressreleases')->getCurrentFilter();
    }
}
