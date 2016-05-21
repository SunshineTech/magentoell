<?php
/**
 * Separation Degrees One
 *
 * Adds visibility filter to Itoris Multiple Wishlist
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_ItorisMWishlist
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_ItorisMWishlist_Block_Frontview class
 */
class SDM_ItorisMWishlist_Block_Frontview extends Itoris_MWishlist_Block_Frontview
{
    protected $_hasWishlistItems;
    protected $_tabId;

    /**
     * Prepare collection
     *
     * @return SDM_ItorisMWishlist_Block_Frontview
     */
    protected function _prepareCollection()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customerId = $customer->getId();
        $wishlist = Mage::getModel('wishlist/wishlist');
        $wishlist->loadByCustomer($customer, true);

        $tableItoris = Mage::getSingleton('core/resource')->getTableName($this->table);
        $version = Mage::getVersion();

        $_tabId = (int)$this->getRequest()->getParam('tabId');
        if (!$_tabId) {
            /**
             * @var $wishlistModel Itoris_MWishlist_Model_Mwishlistnames
             */
            $wishlistModel = Mage::getModel('itoris_mwishlist/mwishlistnames');

            $_tabId = (int)$wishlistModel->checkMainWishlist($customerId);
        }
        $this->_tabId = $_tabId;

        $collection = null;
        $collection = Mage::getResourceModel('itoris_mwishlist/item_collection');
        $collection->addWishlistFilter($wishlist);
        $collection->getSelect()->join($tableItoris, "wishlist_item_id = $tableItoris.item_id");
        $collection->getSelect()->where("multiwishlist_id = $_tabId");
        $collection->setOrderByProductAttribute('name', 'asc');
        $collection->getSelect()->group('main_table.wishlist_item_id');

        // Join product table and filter by visibility
        Mage::helper('sdm_catalog')->addVisibleInSiteToGenericCollection(
            $collection,
            'main_table.product_id'
        );

        // Add discount type to products
        Mage::helper('sdm_catalog')->addDiscountTypeAppliedToCollection(
            $collection,
            "main_table.product_id"
        );

        if ($collection) {
            if ($collection->getSize()) {
                $this->_hasWishlistItems = true;
            } else {
                $this->_hasWishlistItems = false;
            }
            $this->wishlistItemsCollection = $collection;
        }
        return $this;
    }

    /**
     * Has wishlist items
     *
     * @return boolean
     */
    public function getHasWishlistItems()
    {
        return $this->_hasWishlistItems;
    }

    /**
     * Table ID
     *
     * @return integer
     */
    public function getTabId()
    {
        return $this->_tabId;
    }

    /**
     * Get remaining product qty and check threshold value
     * from default Catalog -> Inventory -> Stock Options -> Threshold
     *
     * @param mixed $item
     *
     * @return Mage_CatalogInventory_Model_Stock_Item
     */
    public function getStockQty($item)
    {
        $stockLevel = (int)Mage::getModel('cataloginventory/stock_item')
            ->loadByProduct($item)
            ->getQty();

        if ($stockLevel <=Mage::getStoreConfig('cataloginventory/options/stock_threshold_qty')
        ) {
            return $stockLevel;
        }
    }
}
