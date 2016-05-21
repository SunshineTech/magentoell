<?php
/**
 * Separation Degrees One
 *
 * Add ability to set a limit via Admin instead of statically set in the Core file.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Crosssell
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */
/**
 * SDM_Crosssell_Block_Cart_Crosssell class
 */
class SDM_Crosssell_Block_Cart_Crosssell
    extends Aitoc_Aitquantitymanager_Block_Rewrite_CheckoutCartCrosssell
{
    /**
     * Get crosssell items
     *
     * @return array
     */
    public function getItems()
    {
        $items = $this->getData('items');
        if (is_null($items)) {
            $items = array();
            $ninProductIds = $this->_getCartProductIds();
            if ($ninProductIds) {
                $lastAdded = (int) $this->_getLastAddedProductId();
                if ($lastAdded) {
                    $collection = $this->_getCollection()
                        ->addProductFilter($lastAdded);
                    if (!empty($ninProductIds)) {
                        $collection->addExcludeProductFilter($ninProductIds);
                    }
                    $collection->setPositionOrder()->load();

                    foreach ($collection as $item) {
                        $ninProductIds[] = $item->getId();
                        $items[] = $item;
                    }
                }

                if (count($items) < $this->getCrosssellLimit()) {
                    $filterProductIds = array_merge($this->_getCartProductIds(), $this->_getCartProductIdsRel());
                    $collection = $this->_getCollection()
                        ->addProductFilter($filterProductIds)
                        ->addExcludeProductFilter($ninProductIds)
                        ->setPageSize($this->getCrosssellLimit()-count($items))
                        ->setGroupBy()
                        ->setPositionOrder()
                        ->load();
                    foreach ($collection as $item) {
                        $items[] = $item;
                    }
                }
            }

            $this->setData('items', $items);
        }
        return $items;
    }

    /**
     * Get remaining product qty and check threshold value
     * from default Catalog -> Inventory -> Stock Options -> Threshold
     * @return Mage_CatalogInventory_Model_Stock_Item
     */
    public function getCrosssellLimit()
    {
        if (Mage::getStoreConfig('sdm_crosssell/general/enabled')) {
            $crosssellLimit = Mage::getStoreConfig('sdm_crosssell/general/limit');
            return $crosssellLimit;
        }
    }
}
