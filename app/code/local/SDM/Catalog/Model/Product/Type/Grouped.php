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
 * SDM_Catalog_Model_Product_Type_Grouped class
 */
class SDM_Catalog_Model_Product_Type_Grouped extends Mage_Catalog_Model_Product_Type_Grouped
{
    /**
     * Retrieve collection of associated products
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link_Product_Collection
     */
    public function getAssociatedProductCollection($product = null)
    {
        $collection = $this->getProduct($product)->getLinkInstance()->useGroupedLinks()
            ->getProductCollection()
            ->setFlag('require_stock_items', true)
            ->setFlag('product_children', true)
            ->setIsStrongMode();
        $collection->setProduct($this->getProduct($product));

        if (Mage::app()->getStore()->getCode() != Mage_Core_Model_Store::ADMIN_CODE) {
            $visibility = Mage::getModel('catalog/product_visibility');
            $visibility->addVisibleInSiteFilterToCollection($collection);
        }

        return $collection;
    }
}
