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
 * SDM_Solr_Block_Product_List class
 *
 * This is a stripped down version of the product list which is shown
 * to users when, for some reason, the SOLR server cannot be reached.
 *
 * This does not include any crumb functionality, along with other features.
 */
class SDM_Solr_Block_Product_List extends Mage_Catalog_Block_Product_List
{
    /**
     * Get the catalog url without crumbs
     *
     * @return [string]
     */
    public function getCrumbFreeUrl()
    {
        return null;
    }

    /**
     * Returns an array of breadcrumbs from the catalog crumb model
     *
     * @return array
     */
    public function getCrumbTrail()
    {
        return null;
    }

    /**
     * Gets the current crumb hash based off the page filters
     *
     * @return string
     */
    public function getCrumbHash()
    {
        return null;
    }

    /**
     * Gets the current crumb hash based off the page filters
     *
     * @return string
     */
    public function getCrumbBaseUrl()
    {
        return null;
    }

    /**
     * Get's the last crumb from the crumb trail if applicable
     *
     * @return bool|SDM_Taxonomy_Model_Item
     */
    public function getLastCrumb()
    {
        return null;
    }

    /**
     * Get's the last crumb's type from the crumb trail if applicable
     *
     * @return bool|SDM_Taxonomy_Model_Item
     */
    public function getLastCrumbType()
    {
        return null;
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
        return null;
    }

    /**
     * Get the number of filtered items of a particular product entity type
     *
     * @param  string $type
     * @return int
     */
    public function getCollectionTypeCount($type)
    {
        $type = Mage::helper('sdm_catalog')->getCatalogFilterType($type);

        // Clone the object and reset unneccesary parts for count
        $collection = clone $this->_getProductCollection();
        $collection->addTypeIdFilter($type);
        $collection->removePriceFilter();   // For grouped products by default

        // Preprate the select to count simple/group products
        $select = $collection->getSelect();
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::DISTINCT);
        $select->columns('COUNT(DISTINCT(e.entity_id)) AS count');
        // Mage::log($select->__toString());

        $result = $select->query()->fetch();

        return (int)$result['count'];
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

        // Remove price and special tag filters from projects tab
        if ($type == SDM_Catalog_Helper_Data::IDEA_CODE) {
            $urlParams['_query'] += array('price' => null, 'tag_special' => null);
        }

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
