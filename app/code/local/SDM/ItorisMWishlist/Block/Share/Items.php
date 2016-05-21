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
 * SDM_ItorisMWishlist_Block_Share_Items class
 */
class SDM_ItorisMWishlist_Block_Share_Items extends Itoris_MWishlist_Block_Share_Items
{
    /**
     * Get items
     *
     * @return mixed
     */
    public function getWishlistItems()
    {
        $shareWishlistId = Mage::getSingleton('customer/session')->getData('share_wishlist_id');
        if (is_null($this->_collection)) {
            $this->_collection = $this->_getWishlist()
                ->getItemCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId());
            $this->_collection
                ->getSelect()
                ->join(array(
                            'wishlists' => Mage::getSingleton('core/resource')->getTableName('itoris_mwishlist_items')
                       ),
                       'main_table.wishlist_item_id = wishlists.item_id and wishlists.multiwishlist_id = '
                            . $shareWishlistId
                );

            // Join product table and filter by visibility
            Mage::helper('sdm_catalog')->addVisibleInSiteToGenericCollection(
                $this->_collection,
                'main_table.product_id'
            );

            Mage::helper('sdm_catalog')->addDiscountTypeAppliedToCollection(
                $this->_collection,
                "main_table.product_id"
            );

            $this->_prepareCollection($this->_collection);
        }

        return $this->_collection;
    }
}
