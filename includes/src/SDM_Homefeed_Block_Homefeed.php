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
 * SDM_Homefeed_Block_Homefeed class
 */
class SDM_Homefeed_Block_Homefeed extends Mage_Catalog_Block_Product_List
{
    /**
     * Holds the current collection of products
     *
     * @var Mage_Catalog_Model_Resource_Product_Collection
     */
    protected $_productCollection = null;

    /**
     * Number of products to show
     */
    const LIMIT = 16;

    /**
     * How many columns we should render
     */
    const COLUMN_COUNT = 4;

    public function getBlockTitle()
    {
        return Mage::helper('sdm_homefeed')->getBlockTitle();
    }

    /**
     * Collection getter
     *
     * @return mixed
     */
    public function getLoadedProductCollection()
    {
        return $this->_getProductCollection();
    }

    /**
     * Force rendering to 4 columns
     *
     * @return int
     */
    public function getColumnCount()
    {
        return self::COLUMN_COUNT;
    }

    /**
     * Don't return any toolbar data
     *
     * @return string
     */
    public function getToolbarHtml()
    {
        return '';
    }

    /**
     * Use when blocks override the catalog product list and want to
     * disable the catalog tabs
     *
     * @return boolean
     */
    public function hasCatalogTabs()
    {
        return false;
    }

    /**
     * Override to always show as grid
     *
     * @return string
     */
    public function getMode()
    {
        return 'grid';
    }

    /**
     * Returns the homefeed product collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _getProductCollection()
    {
        if ($this->_productCollection === null) {
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToFilter('homefeed_product', array('eq' => '1'))
                ->applyRequiredAttributes();

            $visibility = Mage::getModel('catalog/product_visibility');
            $visibility->addVisibleInCatalogFilterToCollection($collection);

            $collection->getSelect()->orderRand('e.entity_id')->limit(self::LIMIT);

            $this->_productCollection = $collection;
        }

        return $this->_productCollection;
    }

    /**
     * Remove all the toolbar functionality from _beforeToHtml()
     * to prevent rogue pagiation and sorting
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->_getProductCollection()->load();

        return $this;
    }
}
