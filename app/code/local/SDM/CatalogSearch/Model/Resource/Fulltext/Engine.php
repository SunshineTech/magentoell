<?php
/**
 * Separation Degrees One
 *
 * Magento catalog search customizations
 *
 * Impoartant:
 * Note that this is a rewrite of Mage_CatalogSearch_Model_Resource_Fulltext_Collection.
 * It extends from SDM_Catalog_Model_Resource_Product_Collection just to have
 * all of the custom methods. This class includes all of the methods from
 * Mage_CatalogSearch_Model_Resource_Fulltext_Collection.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CatalogSearch
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_CatalogSearch_Model_Resource_Fulltext_Engine class
 */
class SDM_CatalogSearch_Model_Resource_Fulltext_Engine
    extends Mage_CatalogSearch_Model_Resource_Fulltext_Engine
{
    /**
     * Retrieve allowed visibility values for current engine
     *
     * @return array
     */
    public function getAllowedVisibility()
    {
        return Mage::getModel('catalog/product_visibility')->getVisibleInSiteIds();
    }
}
