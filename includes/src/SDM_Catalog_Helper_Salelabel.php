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
 * SDM_Catalog_Helper_Salelabel class
 */
class SDM_Catalog_Helper_Salelabel extends Mage_Core_Helper_Abstract
{

    /**
     * Creates a sale label block, assigns a product, and returns html
     *
     * @param  mixed  $product
     * @param  string $type
     * @param  mixed  $discountTypeApplied
     * @return string
     */
    public function getSaleLabelHtml($product, $type = 'icon', $discountTypeApplied = null)
    {
        $html = '';

        // If on ERUS, ensure retailer application is approved
        if (Mage::helper('sdm_core')->isSite(SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_RE)) {
            if (!Mage::getSingleton('customer/session')->getCustomer()->isApprovedRetailer()) {
                return $html;
            }
        }

        // Create block and get html if we have product
        if ($product instanceof Mage_Catalog_Model_Product) {
            $html = Mage::app()->getLayout()->createBlock('sdm_catalog/product_salelabel')
                ->setDisplayType($type)
                ->setDiscountTypeApplied($discountTypeApplied)
                ->setProduct($product)
                ->toHtml();
        }

        return trim($html);
    }

    /**
     * Get the applicable sale or new label to show
     *
     * @param  mixed $product
     * @param  mixed $discountTypeApplied
     * @return string
     */
    public function getSaleLabelCode($product, $discountTypeApplied = null)
    {
        $discountTypeApplied = empty($discountTypeApplied) ? $product->getDiscountTypeApplied() : $discountTypeApplied;

        // What kind of label code are we showing?
        if ($discountTypeApplied == SDM_Catalog_Helper_Data::DISCOUNT_TYPE_APPLIED_CODE_PROMO) {
            $labelCode = 'sale-tag';
        } elseif ($discountTypeApplied == SDM_Catalog_Helper_Data::DISCOUNT_TYPE_APPLIED_CODE_SPECIAL_PRICE) {
            $labelCode = 'starburst';
        } elseif ($product->isNewProduct()) {
            $labelCode = 'new-label';
        } else {
            $labelCode = '';
        }

        // This rewrites the previous labels on ERUS, so we're disabling for now until
        // Ellison introduces new labels for these scenarios.
        //
        // if (Mage::helper('sdm_core')->isSite(SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_RE)) {
        //     $retailerPrice = Mage::helper('customerdiscount/price')
        //         ->getRetailerPrice($product);
        //     if ($retailerPrice <= $product->getFinalPrice()) {
        //         $labelCode = 'retailer-discount';
        //     }

        //     $negotiatedPrice = Mage::helper('customerdiscount/price')
        //         ->getNegotiatedPrice($product);
        //     if ($negotiatedPrice <= $product->getFinalPrice()) {
        //         $labelCode = 'negotiated-product';
        //     }
        // }

        return $labelCode;
    }

    /**
     * Get the current sale %
     *
     * @param  mixed $product
     * @return string
     */
    public function getStarburstPercentage($product)
    {
        $perc = round((1 - ($product->getFinalPrice()/$product->getPrice())) * 100);
        $perc = max(0, $perc);
        $perc = min(100, $perc);
        return $perc === 0 ? '' : $perc;
    }
}
