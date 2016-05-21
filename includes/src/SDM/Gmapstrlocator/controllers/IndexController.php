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
 * SDM_Gmapstrlocator_IndexController class
 */
class SDM_Gmapstrlocator_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Ajax action
     *
     * @return void
     */
    public function ajaxAction()
    {
        $return = array();
        foreach ($this->_getStores() as $store) {
            $parsed = array(
                'id'                => $store->getId(),
                'name'              => $store->getStoreName(),
                'number'            => $store->getStoreNumber(),
                'lat'               => $store->getLatitude(),
                'lng'               => $store->getLongitude(),
                'type'              => $store->getStoreType(),
                'design_center'     => $store->getHasDesignCenter(),
                'product_lines'     => $store->getProductLines(),
                'agent_type'        => $store->getAgentType(),
                'rep_serving'       => $store->getRepresentativeServing(),
                'image'             => $store->getImage(),
                'address'           => $store->getAddress(),
                'address2'          => $store->getAddress2(),
                'city'              => $store->getCity(),
                'state'             => $store->getState(),
                'postal_code'       => $store->getPostalCode(),
                'country'           => $store->getCountry(),
                'phone'             => $store->getStorePhone(),
                'fax'               => $store->getStoreFax(),
                'email'             => $store->getStoreEmail(),
                'website'           => $store->getStoreWebsite(),
                'distance'          => round($store->getDistance(), 1)
            );
            $parsed['pretty_address'] = array_values(
                array_filter(
                    array_map('trim',
                        array(
                            $parsed['address'],
                            $parsed['address2'],
                            $parsed['city'] . (
                                !empty($parsed['city']) && !empty($parsed['state']) ? ", " : ""
                            ) . $parsed['state'] . " " . $parsed['postal_code'],
                            $parsed['country']
                        )
                    )
                )
            );
            $return[] = $parsed;
        }
        $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ->setBody(Mage::helper('core')->jsonEncode($return));
    }

    /**
     * Get stores
     *
     * @return SDM_Gmapstrlocator_Model_Resource_Location_Collection
     */
    protected function _getStores()
    {
        $collection = Mage::getModel('gmapstrlocator/location')->getCollection()
            ->addWebsiteFilter()
            ->addFieldToFilter('main_table.status', 1);

        $listingType   = $this->getRequest()->getParam('listing_type');
        if ($listingType === 'standard') {
            // Apply standard search filters
            $this->_applyStandardListingFilters($collection);
            
            // Only show physical stores
            $collection->getSelect()
                ->where('store_type LIKE "%|physical|%"');

            // Set page limit
            $collection->getSelect()->limit(100);
        } else {
            // Apply online filters and sorting
            $this->_applyOnlineListingFilters($collection);
        }

        return $collection;
    }

    /**
     * Online filters
     *
     * @param SDM_Gmapstrlocator_Model_Resource_Location_Collection $collection
     *
     * @return void
     */
    protected function _applyOnlineListingFilters($collection)
    {
        if (Mage::helper('sdm_core')->isSite(SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_UK)) {
            $collection->getSelect()
                ->where('store_type LIKE "%|online|%" OR store_type LIKE "%|catalog|%"')
                ->order(
                    '(`country` = "United Kingdom") DESC,'.
                    '(`country` = "United States")  DESC,'.
                    ' `country` ASC, store_name ASC'
                );
        } else {
            $collection->getSelect()
                ->where('store_type LIKE "%|online|%" OR store_type LIKE "%|catalog|%"')
                ->order(
                    '(`country` = "United States")  DESC,'.
                    '(`country` = "United Kingdom") DESC,'.
                    ' `country` ASC, store_name ASC'
                );
        }
    }

    /**
     * Standard filters
     *
     * @param SDM_Gmapstrlocator_Model_Resource_Location_Collection $collection
     *
     * @return void
     */
    protected function _applyStandardListingFilters($collection)
    {
        switch ($this->getRequest()->getParam('search_type')) {
            case 'name':
                $this->_applyNameFilters($collection);
                break;
            case 'location':
                $this->_applyLocationFilters($collection);
        }
    }

    /**
     * Apply filters to collection for name search
     *
     * @param SDM_Gmapstrlocator_Model_Resource_Location_Collection $collection
     *
     * @return void
     */
    protected function _applyNameFilters($collection)
    {
        $searchName    = $this->getRequest()->getParam('search_name');
        $searchCountry = $this->getRequest()->getParam('search_country');

        // Add name filter
        if (!empty($searchName)) {
            $searchName = addslashes($searchName);
            $collection->getSelect()->where('store_name LIKE "%'.$searchName.'%"');
        }

        // Search country
        if (!empty($searchCountry)) {
            $collection->getSelect()->where('country="'.$searchCountry.'"');
        }
    }

    /**
     * Apply filters to collection for location search
     *
     * @param SDM_Gmapstrlocator_Model_Resource_Location_Collection $collection
     *
     * @return void
     */
    protected function _applyLocationFilters($collection)
    {
        $sLat         = $this->getRequest()->getParam('search_lat');
        $sLng         = $this->getRequest()->getParam('search_lng');
        $searchState  = $this->getRequest()->getParam('search_state');
        $searchRadius = $this->getRequest()->getParam('search_radius');

        // Verify lat/lng; fall back to default otherwise
        if ($sLat === 'no' || empty($sLat) || $sLng === 'no' || empty($sLng)) {
            $sLat = Mage::helper('gmapstrlocator')->getGMapStandardLatitude();
            $sLng = Mage::helper('gmapstrlocator')->getGMapStandardLongitude();
        }

        // Add lat/lng filter
        $collection->getSelect()->columns(
            array(
                'distance' => '( 3959 * acos( cos( radians('
                    . $sLat . ') ) * cos( radians( latitude ) ) * cos( radians(longitude) - radians('
                    . $sLng . ')) + sin(radians(' . $sLat . ')) * sin( radians(latitude))))'
            )
        );

        // Add having condition for radius and state
        $searchRadius = (int)$searchRadius;
        $searchRadius = $searchRadius < 5 ? 5 : ($searchRadius > 100 ? 100 : $searchRadius);
        $having = 'distance <= '.$searchRadius;
        if (!empty($searchState) && $searchState !== 'no') {
            $searchState = trim(addslashes($searchState));
            $having .= ' OR representative_serving LIKE "%|'.$searchState.'|%"';
        }
        $collection->getSelect()->having($having);

        $collection->getSelect()
            ->order('distance');
    }
}
