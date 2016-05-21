<?php
/**
 * Separation Degrees One
 *
 * Handles designer page and designer article rendering
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_News
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_News_Block_Filter class
 */
class SDM_News_Block_Filter extends Mage_Core_Block_Template
{
    /**
     * Get filters
     *
     * @return mixed
     */
    public function getFilters()
    {
        return Mage::helper('sdm_news')->getNewsArticleDates();
    }

    /**
     * Current filter
     *
     * @return mixed
     */
    public function getCurrentFilter()
    {
        return Mage::helper('sdm_news')->getCurrentFilter();
    }
}
