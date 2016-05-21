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
 * SDM_Catalog_Helper_Buybox class
 */
class SDM_Catalog_Helper_Buybox
    extends Mage_Core_Helper_Abstract
{
    /**
     * Checks various product settings (lifecycle, orderability, qty, etc.)
     * and returns an array that specifies various portions of the resulting
     * button text/buy box logic. The keys of the array are as follows:
     *
     *  (string) message     => Contains the buybox message
     *  (bool)   onSale      => Is this product on sale?
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array
     */
    public function getBuyBoxData($product)
    {
        if (empty($product) && !$product instanceof Mage_Catalog_Model_Product) {
            return array();
        }

        // Product attributes used to formulate the rest of our purchase logic
        $data = array(
            'life_cycle'      => strtolower($product->getAttributeText('life_cycle')), // String
            'orderable'       => strtolower($product->getAttributeText('is_orderable')) == 'yes',
            'instore'         => strtolower($product->getAttributeText('in_store')) == 'yes',
            'qty'             => $product->getStockItem()->getQty(), //Float
            'inStock'         => $product->getStockItem()->getIsInStock(),
            'backorder'       => strtolower($product->getAttributeText('is_backorderable')) == 'yes',
            'checkout_backorder' => $product->isBackorderable(),
            'purchaseHold'    => strtolower($product->getAttributeText('purchase_hold')) == 'yes',
            'type'            => strtolower($product->getAttributeText('product_type')), //String
            'preOrder'        => strtolower($product->getAttributeText('is_preorderable')) == 'yes',
            'isNew'           => $product->isNewProduct(),
            'isRetailer'      => $this->_getIsRetailer(),
            'isUk'            => $this->_getIsUk(),
            'onSale'          => $this->_checkIfOnPromo($product)
        );

        return $this->_getBuyBoxMessage($data);
    }

    /**
     * Checks if the product is on sale
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return bool
     */
    protected function _checkIfOnPromo($product)
    {
        return $product->getDiscountTypeApplied()
            === SDM_Catalog_Helper_Data::DISCOUNT_TYPE_APPLIED_CODE_PROMO;
    }

    /**
     * Runs conditional logic to create our buy box message
     *
     * @param  array $data An array of product data provided from getPurchaseLogic()
     * @return string The buy box message
     */
    protected function _getBuyBoxMessage($data)
    {
        $message = "";

        // Create "easy" booleans to simplify our conditionals
        $preventOnSaleMessage = false;
        $PR = $data['life_cycle'] == 'pre-release';
        $AC = $data['life_cycle'] == 'active';
        $DC = $data['life_cycle'] == 'discontinued';
        $IC = $data['life_cycle'] == 'inactive';
        $orderable = (bool)$data['orderable'];
        $backOrderable = (bool)$data['backorder'];
        $preOrderable = (bool)$data['preOrder'];
        $purchaseHold = (bool)$data['purchaseHold'];
        $inStore = (bool)$data['instore'];
        $isNew = (bool)$data['isNew'];
        $isRetailer = (bool)$data['isRetailer'];
        $inStock = ($data['qty'] > 0 && $data['inStock']) || $data['checkout_backorder'];
        $isBundle = $data['type'] == 'bundle';
        $isDieSet = $data['type'] == 'die set';
        $isUk = (bool)$data['isUk'];
        $onSale = (bool)$data['onSale'];

        // If retailer, make sure logged in and approved
        if ($isRetailer) {
            if (!Mage::getSingleton('customer/session')->getCustomer()->isApprovedRetailer()) {
                return "";
            }
        }

        // Condition #1
        if (($PR || $AC) && !$orderable && !$inStore && $isNew) {
            $message = "Coming Soon";
            $preventOnSaleMessage = true;

            // Condition #2
        } elseif (($PR || $AC) && !$orderable && $inStore && $isNew) {
            $message = "In Stores Now!";
            $preventOnSaleMessage = true;

            // Condition #3
        } elseif (($AC || $DC) && !$orderable && !$isNew) {
            $message = "";
            $preventOnSaleMessage = true;

            // Condition #4
        } elseif ($isRetailer && (($PR && $orderable) || ($AC && $preOrderable))) {
            $message = "Pre-order Now";

            // Condition #5
        } elseif (($AC || $DC) && $inStock && !$purchaseHold && !$preOrderable && !$isBundle && $isNew) {
            $message = $isUk ? "" : "New Arrival. Buy Now";

            // Condition #6
        } elseif (($AC || $DC) && $inStock && !$purchaseHold && !$preOrderable && !$isBundle) {
            $message = $isUk ? "" : "Buy Now";

            // Condition #7
        } elseif (($AC || $DC) && $inStock && $purchaseHold && !$preOrderable && !$isBundle) {
            $message = $isUk ? "" : "Buy Now<br>(while supplies last)";

            // Condition #8
        } elseif (($AC && ($isBundle || $isDieSet) && $inStock) || ($DC && $isBundle && $inStock)) {
            $message = $isUk ? "" : "Buy Now<br>(while supplies last)";

            // Condition #9
        } elseif (($AC) && !$inStock && !$preOrderable && $orderable && !$backOrderable) {
            $message = "Out of Stock<br>(Add to Wishlist)";

            // Condition #10
        } elseif (($IC) || ($DC && !$inStock && $orderable)) {
            $message = "No Longer Available";
        }

        // Condition #11 (overrides conditions 4-10)
        if (!$preventOnSaleMessage && $onSale) {
            $message = "On Sale Today<br>(While Supplies Last)";
        }

        return $message;
    }

    /**
     * Checks if we're on a retailer site.
     *
     * @return bool
     */
    protected function _getIsRetailer()
    {
        return Mage::helper('sdm_core')->isSite(SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_RE);
    }

    /**
     * Checks if we're currently on the UK site (for differing message logic)
     *
     * @return bool
     */
    protected function _getIsUk()
    {
        return Mage::helper('sdm_core')->isSite(SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_UK);
    }
}
