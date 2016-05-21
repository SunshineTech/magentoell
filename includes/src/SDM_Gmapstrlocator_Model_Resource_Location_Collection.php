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
 * SDM_Gmapstrlocator_Model_Resource_Location_Collection class
 */
class SDM_Gmapstrlocator_Model_Resource_Location_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
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

    /**
     * Add website filter to collection
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return SDM_Gmapstrlocator_Model_Resource_Location_Collection
     */
    public function addWebsiteFilter($website = null)
    {
        if ($website instanceof Mage_Core_Model_Website) {
            $website = array($website->getId());
        } else {
            $website = Mage::app()->getWebsite()->getId();
        }

        $this->getSelect()->join(
            array('website_table' => $this->getTable('gmapstrlocator_website')),
            'main_table.gmapstrlocator_id = website_table.gmapstrlocator_id',
            array()
        )
            ->where('website_table.website_id in (?)', array(0, $website));

        return $this;
    }

    /**
     * Get collection size override for this model, since we use groupings in the admin panel
     * and the original getSize() function doesn't play nice with that.
     *
     * Side effect is that getSize() now has to load the entire collection to get the size :/
     *
     * Oh well.
     *
     * @return int
     */
    public function getSize()
    {
        if (is_null($this->_totalRecords)) {
            $sql = $this->getSelect();
            $this->_totalRecords = count($this->getConnection()->fetchCol($sql));
        }
        return intval($this->_totalRecords);
    }
}
