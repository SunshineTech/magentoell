<?php
/**
 * Separation Degrees One
 *
 * Implements the customer/retailer discount logic for viewing and obtaining
 * discounts and prices, as wel as managing the customer groups.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CustomerDiscount
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_CustomerDiscount_Model_Product class
 */
class SDM_CustomerDiscount_Model_Product extends SDM_Catalog_Model_Product
{
    /**
     * Get product final price
     *
     * Note: Unsure why this was placed here. YK.
     *
     * @param double $qty
     *
     * @return double
     */
    // public function getFinalPrice($qty=null)
    // {
    //     $price = $this->_getData('final_price');

    //     if ($price !== null) {
    //         return $price;
    //     }

    //     $price = $this->getPriceModel()->getFinalPrice($qty, $this);

    //     return $price;
    // }
}
