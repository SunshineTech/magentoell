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

// Ultimately extends Mage_Catalog_Model_Product_Indexer_Price
/**
 * SDM_CustomerDiscount_Model_Product_Indexer_Price class
 */
class SDM_CustomerDiscount_Model_Product_Indexer_Price
    extends Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogProductIndexerPrice
{
    /**
     * Initialize
     *
     * @return void
     */
    protected function _construct()
    {
        // Correct Aitoc's resource model pointer
        $this->_init('catalog/product_indexer_price');
    }
}
