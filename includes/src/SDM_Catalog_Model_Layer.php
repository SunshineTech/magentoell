<?php
/**
 * Separation Degrees One
 *
 * Magento catalog customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Catalog
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Catalog_Model_Layer class
 */
class SDM_Catalog_Model_Layer extends IntegerNet_Solr_Model_Catalog_Layer
{
    /**
     * Retrieve current layer product collection and override the prices if
     * necessary.
     *
     * This is done here instead of Mage_Catalog_Model_Category because
     * it seems to be too early to apply custom prices.
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    public function getProductCollection()
    {
        if (isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
            $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
        } else {
            $collection = $this->getCurrentCategory()->getProductCollection();
            $this->prepareProductCollection($collection);
            $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;

            /**
             * Rewrite begins
             */
            $collection
                ->addTypeIdFilter()
                ->addDiscountTypeAppliedTags()
                ->addEuroPrices()
                ->removePriceFilter();
            // Mage::log($collection->getSelect()->__toString());
            /**
             * Rewrite ends
             */
        }

        return $collection;
    }
}
