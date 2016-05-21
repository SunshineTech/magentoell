<?php
/**
 * Separation Degrees Media
 *
 * Ellison's Mage_Sales customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Sales
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Sales_Model_Order_Item class
 */
class SDM_Sales_Model_Order_Item extends Mage_Sales_Model_Order_Item
{
    /**
     * Returns the product_type ID
     *
     * @param boolean $getString
     *
     * @return int
     */
    public function getItemProductType($getString = false)
    {
        if ($this->getItemType()) {
            if ($getString) {
                return $this->getItemType();    // Note: not developed to return string yet
            } else {
                return $this->getItemType();
            }

        } else {
            $product = Mage::getModel('catalog/product')->load($this->getProductId());
            if ($getString) {
                return $product->getProductType();  // Note: not developed to return string yet
            } else {
                return $product->getProductType();
            }
        }
    }
}
