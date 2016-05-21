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
 * SDM_CatalogInventory_Model_Stock_Item class
 */
class SDM_CatalogInventory_Model_Stock_Item
    extends Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogInventoryStockItem
{
    /**
     * Is item in stock
     *
     * @return boolean
     */
    public function getIsInStock()
    {
        // Always return true
        return true;
    }

    /**
     * Before saving
     *
     * @return SDM_CatalogInventory_Model_Stock_Item
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        // Always set is_in_stock true
        $this->setIsInStock(true);

        return $this;
    }
}
