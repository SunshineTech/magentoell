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
 * SDM_CatalogSearch_Model_Resource_Fulltext_Collection class
 */
class SDM_CatalogSearch_Model_Resource_Fulltext_Collection extends SDM_Catalog_Model_Resource_Product_Collection
{
    // Rewrites begins

    // Rewrites ends

    /**
     * Methods below are copied from Mage_CatalogSearch_Model_Resource_Fulltext_Collection
     * without any modifications.
     *
     * Do not modify any of the methods below unless am explicit rewrite is
     * required.
     */

    /**
     * Retrieve query model object
     *
     * @return Mage_CatalogSearch_Model_Query
     */
    protected function _getQuery()
    {
        return Mage::helper('catalogsearch')->getQuery();
    }

    /**
     * Add search query filter
     *
     * @param  string $query
     * @return Mage_CatalogSearch_Model_Resource_Fulltext_Collection
     */
    public function addSearchFilter($query)
    {
        if ($this->_getQuery()->getId()) {
            Mage::getSingleton('catalogsearch/fulltext')->prepareResult();

            $this->getSelect()->joinInner(
                array('search_result' => $this->getTable('catalogsearch/result')),
                $this->getConnection()->quoteInto(
                    'search_result.product_id=e.entity_id AND search_result.query_id=?',
                    $this->_getQuery()->getId()
                ),
                array('relevance' => 'relevance')
            );
        }

        return $this;
    }

    /**
     * Set Order field
     *
     * @param  string $attribute
     * @param  string $dir
     * @return Mage_CatalogSearch_Model_Resource_Fulltext_Collection
     */
    public function setOrder($attribute, $dir = 'desc')
    {
        if ($attribute == 'relevance') {
            $this->getSelect()->order("relevance {$dir}");
        } else {
            parent::setOrder($attribute, $dir);
        }
        return $this;
    }

    /**
     * Stub method for campatibility with other search engines
     *
     * @return Mage_CatalogSearch_Model_Resource_Fulltext_Collection
     */
    public function setGeneralDefaultQuery()
    {
        return $this;
    }
}
