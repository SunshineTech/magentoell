<?php
/**
 * Separation Degrees One
 *
 * Ellison Homefeed
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Homefeed
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Homefeed_Helper_Data class
 */
class SDM_Homefeed_Helper_Data extends SDM_Core_Helper_Data
{
    const XML_PATH_TITLE = 'sdm_homefeed/general/title';

    public function getBlockTitle()
    {
        return Mage::getStoreConfig(self::XML_PATH_TITLE, Mage::app()->getStore());
    }
}
