<?php
/**
 * Separation Degrees One
 *
 * Mage_Catalog-related customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Catalog
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Catalog_Model_Resource_Product_Link_Product_Collection
 */
class SDM_Catalog_Model_Resource_Product_Link_Product_Collection
    extends Mage_Catalog_Model_Resource_Product_Link_Product_Collection
{
    /**
     * Add the Euro price to the collection if on the UK Euro store.
     *
     * @return SDM_Catalog_Model_Resource_Product_Collection
     */
    public function addEuroPrices()
    {
        $store = Mage::app()->getStore();
        $storeId = $store->getId();

        if ($store->getCode() == SDM_Core_Helper_Data::STORE_CODE_UK_EU) {
            $this->_overridePrices(SDM_Catalog_Helper_Data::EURO_CODE);
        }

        return $this;
    }

    /**
     * Overrides the price and special_price with the specified currency prices.
     *
     * 'minimal_price' added to prevent strikethrough prices from being displayed.
     *
     * @param str $currency Currency string that is part of the attribute
     *
     * @return void
     */
    public function _overridePrices($currency = null)
    {
        if (!$currency) {
            return;
        }

        $currency = strtolower($currency);

        // Left-join allows the original price and special_price fields to be used
        // in case the custom prices are not indexed
        $select = $this->getSelect()
            ->joinLeft(
                array('cp' => $this->getTable('sdm_catalog/index_custom_price')),
                'cp.entity_id = e.entity_id',
                array('price', 'final_price', 'final_price AS minimal_price')
            );
    }

    /**
     * Applies attributes required for rendering. Serves same purpose as the method
     * below.
     *
     * @see SDM_Catalog_Model_Resource_Product_Collection::applyRequiredAttributes
     *
     * @return SDM_Catalog_Model_Resource_Product_Collection
     */
    public function applyRequiredAttributes()
    {
        return $this->addMinimalPrice()
            ->addFinalPrice()
            // ->addTaxPercents()
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addUrlRewrite()
            ->setPositionOrder()
            ->addStoreFilter()
            ->addEuroPrices();
    }
}
