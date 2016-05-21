<?php
/**
 * Separation Degrees Media
 *
 * Modifications to the IntegerNet_Solr module
 *
 * @category  SDM
 * @package   SDM_Solr
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2016 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Solr_Block_Result_List class
 */
class SDM_Solr_Block_Result_List extends IntegerNet_Solr_Block_Result_List
{
    /**
     * Breadcrumbs
     */
    protected $_crumbs = null;

    public function getLoadedProductCollection()
    {
        // IntegerNet_Solr_Model_Resource_Catalog_Product_Collection
        $collection = parent::getLoadedProductCollection();

        // Yes, we need to loop through this collection to get the IDs instead
        // of using ->getAllIds();
        // Don't ask...
        $ids = array();
        foreach($collection as $product) {
            $ids[] = $product->getId();
        }
        if (!count($ids)) {
            return $collection;
        }

        // Get discount type applied
        $collection = Mage::helper('sdm_catalog')->addDiscountTypeAppliedToSolrCollection(
            $ids,
            $collection
        );

        /**
         * @todo Implement a better solution than this hacky fix for getFinalPrice() on SZUK-Euro
         */
        if ((int)Mage::app()->getStore()->getId() === SDM_Core_Helper_Data::STORE_ID_UK_EU) {
            $fixedPrices = array();
            $badlyLoadedCollection = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToFilter(
                    'entity_id', array('in' => $ids)
                );
            foreach($badlyLoadedCollection as $badlyLoaded) {
                $fixedPrices[$badlyLoaded->getId()] = $badlyLoaded->getFinalPrice();
            }
            foreach($collection as $product) {
                if (isset($fixedPrices[$product->getId()])) {
                    $product->setFinalPrice($fixedPrices[$product->getId()]);
                }
            }
        }

        return $collection;
    }

    /**
     * Get the catalog url without crumbs
     *
     * @return string
     */
    public function getCrumbFreeUrl()
    {
        if (Mage::getSingleton('sdm_catalogcrumb/crumb')->getFilterCount() < 2) {
            return false;
        }
        return "/" . Mage::getStoreConfig('navigation/general/catalog_category_id');
    }

    /**
     * Returns an array of breadcrumbs from the catalog crumb model
     *
     * @return array
     */
    public function getCrumbTrail()
    {
        return Mage::getSingleton('sdm_catalogcrumb/crumb')
            ->getCrumbTrail();
    }

    /**
     * Gets the current crumb hash based off the page filters
     *
     * @return string
     */
    public function getCrumbHash()
    {
        return Mage::getSingleton('sdm_catalogcrumb/crumb')
            ->getData('hash');
    }

    /**
     * Gets the current crumb hash based off the page filters
     *
     * @return string
     */
    public function getCrumbBaseUrl()
    {
        return Mage::getSingleton('sdm_catalogcrumb/crumb')
            ->getCrumbBaseUrl();
    }

    /**
     * Get's the last crumb from the crumb trail if applicable
     *
     * @return bool|SDM_Taxonomy_Model_Item
     */
    public function getLastCrumb()
    {
        return Mage::getSingleton('sdm_catalogcrumb/crumb')
            ->getLastCrumb();
    }

    /**
     * Get's the last crumb's type from the crumb trail if applicable
     *
     * @return bool|SDM_Taxonomy_Model_Item
     */
    public function getLastCrumbType()
    {
        return Mage::getSingleton('sdm_catalogcrumb/crumb')
            ->getLastCrumbType();
    }

    /**
     * Retrieve Type URL
     *
     * @param string $remove
     *
     * @return string
     */
    public function getCrumbUrl($remove)
    {
        $urlParams = $this->_getUrlParams($remove);

        // Implode all the arrayed filters
        foreach ($urlParams as $filter => $filterValues) {
            if (is_array($filterValues)) {
                $urlParams[$filter] = implode(',', $filterValues);
            }
        }

        $urlParams['crumb'] = $this->getCrumbHash();
        return (
                !isset($urlParams['q'])
                    ? DS . $this->getCrumbBaseUrl()
                    : '/catalogsearch/result/'
            ) . '?' . http_build_query($urlParams);
    }

    /**
     * Add all filters to _query except for the excluded one
     *
     * @param string $remove
     *
     * @return array
     */
    protected function _getUrlParams($remove)
    {
        $remove = explode('|', $remove);
        $removeKey = $remove['0'];
        $removeValue = $remove['1'];
        $urlParams = array();
        foreach ($this->getCurrentFilters() as $filterKey => $filterValues) {
            foreach (explode(",", $filterValues) as $filterValue) {
                if ($removeKey != $filterKey || $removeValue != $filterValue) {
                    if (!isset($urlParams[$filterKey])) {
                        $urlParams[$filterKey] = array();
                    }
                    $urlParams[$filterKey][] = $filterValue;
                }
            }
        }
        // Special case for price
        if ($removeKey === 'price') {
            unset($urlParams['price']);
        }
        return $urlParams;
    }

    /**
     * Get all the current filter parameters
     * @return array
     */
    public function getCurrentFilters()
    {
        return Mage::app()->getRequest()->getParams();
    }

    /**
     * Get the number of filtered items of a particular product entity type
     *
     * @param  string $type
     * @return int
     */
    public function getCollectionTypeCount($type)
    {
        // New SOLR solution
        return Mage::getSingleton('integernet_solr/result')->getSolrTypeCount($type);
    }

    /**
     * Gets the current product type filter based off the URL parameter
     *
     * @return string
     */
    public function getCatalogType()
    {
        return Mage::helper('sdm_catalog')->getCatalogType();
    }

    /**
     * Gets the current product filter type name based off URL parameter
     *
     * @param string|null $type
     *
     * @return string
     */
    public function getCatalogTypeName($type = null)
    {
        return Mage::helper('sdm_catalog')->getCatalogTypeName($type);
    }

    /**
     * Checks if a specific type is active, and returns a class name if it is
     *
     * @param  string $type
     * @return string
     */
    public function getActiveClass($type)
    {
        return $this->getCatalogType() == $type ? 'active' : '';
    }

    /**
     * Retrieve Type URL
     *
     * @param  string $type
     * @return string
     */
    public function getTypeUrl($type)
    {
        $urlParams = array();
        $urlParams['_current']      = true;
        $urlParams['_escape']       = true;
        $urlParams['_use_rewrite']  = true;
        $urlParams['_query']        = array('type' => $type);

        // Disabling this because it is apparently causing confusion, but leaving it here
        // as we may want it back.
        // 
        // // Remove price and special tag filters from projects tab
        // if ($type == SDM_Catalog_Helper_Data::IDEA_CODE) {
        //     $urlParams['_query'] += array('price' => null, 'tag_special' => null);
        // }

        // Clean URL by removing uneeded home, no_cache, and p params
        $urlParams['_query'] += array('home' => null, 'no_cache' => null, 'p' => null);

        // Do a custom cleanup of the URL...
        $url = $this->getUrl('*/*/*', $urlParams);
        $urlParts = explode('?', $url);
        if (count($urlParts) === 2) {
            $url  = $this->getUrl();
            if (strpos($urlParts[0], "/catalogsearch/result/index/") === false) {
                $url .= "catalog";
            } else {
                $url .= "catalogsearch/result/index";
            }
            $url .= "?" . $urlParts[1];
        }
        
        return $url;
    }
}
