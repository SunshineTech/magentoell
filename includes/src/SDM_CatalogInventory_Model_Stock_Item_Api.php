<?php
/**
 * Separation Degrees Media
 *
 * Changes to CatalogInventory
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CatalogInventory
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_CatalogInventory_Model_Stock_Item_Api class
 */
class SDM_CatalogInventory_Model_Stock_Item_Api
    extends Aitoc_Aitquantitymanager_Model_Rewrite_CatalogInventoryStockItemApi
{
    /**
     * Update stock item
     *
     * @param  integer $productId
     * @param  array   $data
     * @return void
     */
    public function update($productId, $data)
    {
        // Force is_in_stock to always be true
        $data['is_in_stock'] = 1;
        parent::update($productId, $data);
    }
}
