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
 * SDM_Gmapstrlocator_Helper_Data class
 */
class SDM_Gmapstrlocator_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_GMAP_PAGE_TITLE             = 'gmapstrlocator/general/page_title';
    const XML_GMAP_STORES_TAB_TITLE       = 'gmapstrlocator/general/stores_tab';
    const XML_GMAP_PAGE_METAKEYWORD       = 'gmapstrlocator/general/meta_keywords';
    const XML_GMAP_PAGE_METADESCRIPTION   = 'gmapstrlocator/general/meta_description';
    const XML_GMAP_STANDARD_LATITUDE      = 'gmapstrlocator/general/standard_lat';
    const XML_GMAP_STANDARD_LONGITUDE     = 'gmapstrlocator/general/standard_long';
    const XML_GMAP_API_KEY                = 'gmapstrlocator/general/api_key';
    const XML_GMAP_ZIP_NEAREST_STORE      = 'gmapstrlocator/general/zip_nearest';
    const XML_GMAP_ADDRESS_NEAREST_STORE  = 'gmapstrlocator/general/address_nearest';
    const XML_GMAP_ZOOM                   = 'gmapstrlocator/general/map_zoom';

    /**
     * Config getter
     *
     * @return string
     */
    public function getGMapNearestZipEnabled()
    {
        return Mage::getStoreConfig(self::XML_GMAP_ZIP_NEAREST_STORE);
    }

    /**
     * Config getter
     *
     * @return string
     */
    public function getGMapNearestAddressEnabled()
    {
        return Mage::getStoreConfig(self::XML_GMAP_ADDRESS_NEAREST_STORE);
    }

    /**
     * Config getter
     *
     * @return string
     */
    public function getGMapPageTitle()
    {
        return Mage::getStoreConfig(self::XML_GMAP_PAGE_TITLE);
    }

    /**
     * Config getter
     *
     * @return string
     */
    public function getGMapStoresTabTitle()
    {
        return Mage::getStoreConfig(self::XML_GMAP_STORES_TAB_TITLE);
    }

    /**
     * Config getter
     *
     * @return string
     */
    public function getGMapAPIKey()
    {
        return Mage::getStoreConfig(self::XML_GMAP_API_KEY);
    }

    /**
     * Config getter
     *
     * @return string
     */
    public function getGMapZoom()
    {
        return Mage::getStoreConfig(self::XML_GMAP_ZOOM);
    }

    /**
     * Page url
     *
     * @return string
     */
    public function getStoreLocatorPageUrl()
    {
        return Mage::getUrl('stores');
    }

    /**
     * Config getter
     *
     * @return string
     */
    public function getGMapStandardLatitude()
    {
        return Mage::getStoreConfig(self::XML_GMAP_STANDARD_LATITUDE);
    }

    /**
     * Config getter
     *
     * @return string
     */
    public function getGMapStandardLongitude()
    {
        return Mage::getStoreConfig(self::XML_GMAP_STANDARD_LONGITUDE);
    }

    /**
     * Search and replace recursively
     *
     * @param string $search
     * @param string $replace
     * @param array  $subject
     *
     * @return array
     */
    public function recursiveReplace($search, $replace, $subject)
    {
        if (!is_array($subject)) {
            return $subject;
        }
        foreach ($subject as $key => $value) {
            if (is_string($value)) {
                $subject[$key] = str_replace($search, $replace, $value);
            } elseif (is_array($value)) {
                $subject[$key] = self::recursiveReplace($search, $replace, $value);
            }
        }
        return $subject;
    }

    /**
     * Get filter
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function getWysiwygFilter($data)
    {
        $helper = Mage::helper('cms');
        $processor = $helper->getPageTemplateProcessor();
        return $processor->filter($data);
    }

    /**
     * Product lines
     *
     * @return array
     */
    public function getProductLines()
    {
        return Mage::getModel('gmapstrlocator/system_config_source_productlines')->toOptionArray();
    }

    /**
     * Returns the store type associative array
     *
     * @return array
     */
    public function getStoreTypes()
    {
        return SDM_Gmapstrlocator_Model_System_Config_Source_Storetypes::toOptionArray();
    }
}
