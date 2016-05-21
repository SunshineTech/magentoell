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
 * SDM_Catalog_Model_Product_Type_Price class
 */
class SDM_Catalog_Model_Product_Type_Price
    extends Mage_Catalog_Model_Product_Type_Price
{
    /**
     * Default action to get price of product
     *
     * Rewiritten to return Euro price when appropriate
     *
     * @param mixed $product
     *
     * @return decimal
     */
    public function getPrice($product)
    {
        if ($product->getStoreId() == SDM_Core_Helper_Data::STORE_ID_UK_EU) {
            return $product->getData('price_euro');
        } else {
            return $product->getData('price');
        }
    }

    /**
     * Retrieve product final price
     *
     * @param float|null                 $qty
     * @param Mage_Catalog_Model_Product $product
     *
     * @return float
     */
    public function getFinalPrice($qty = null, $product = false)
    {
        if (is_null($qty) && !is_null($product->getCalculatedFinalPrice())) {
            return $product->getCalculatedFinalPrice();
        }

        if (Mage::app()->getStore()->getCode() == SDM_Core_Helper_Data::STORE_CODE_UK_EU) {
            $finalPrice = $this->getBasePriceEuro($product, $qty);
        } else {
            $finalPrice = $this->getBasePrice($product, $qty);
        }

        $product->setFinalPrice($finalPrice);

        Mage::dispatchEvent(
            'catalog_product_get_final_price',
            array('product' => $product, 'qty' => $qty)
        );

        $finalPrice = $product->getData('final_price');
        $finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);
        $finalPrice = max(0, $finalPrice);
        $product->setFinalPrice($finalPrice);

        return $finalPrice;
    }

    /**
     * Get base price with apply Group, Tier, Special prises
     *
     * @param Mage_Catalog_Model_Product $product
     * @param float|null                 $qty
     *
     * @return float
     */
    public function getBasePriceEuro($product, $qty = null)
    {
        $price = (float)$product->getPriceEuro();

        // This should not happen, but it does in rare cases for unknown reason.
        // Load the regular Euro price to avoid displaying 0.00 Euro.
        if (!$price) {
            $price = Mage::getModel('catalog/product')->load($product->getId())
                ->getPriceEuro();
        }

        // Return only the special price taken into account because other
        // prices aren't developed for the UK Euro site.
        return $this->_applySpecialPriceEuro($product, $price);
    }

    /**
     * Apply special price for product if not return price that was before
     *
     * @param Mage_Catalog_Model_Product $product
     * @param float                      $finalPrice
     *
     * @return float
     */
    protected function _applySpecialPriceEuro($product, $finalPrice)
    {
        return $this->calculateSpecialPrice(
            $finalPrice,
            $product->getSpecialPriceEuro(),
            $product->getSpecialFromDate(),
            $product->getSpecialToDate(),
            $product->getStore()
        );
    }
}
