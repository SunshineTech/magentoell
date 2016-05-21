<?php
/**
 * Separation Degrees Media
 *
 * Allows saving quotes that can be later be converted into orders with preserved
 * pricing.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_SavedQuote
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_SavedQuote_Model_Savedquote_Item class
 */
class SDM_SavedQuote_Model_Savedquote_Item extends Mage_Core_Model_Abstract
{
    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('savedquote/savedquote_item');
    }

    /**
     * Retrieve product model object associated with the item
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        $product = $this->_getData('product');

        // Caching needed; invoked multiple times
        if ($product === null && $this->getProductId()) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::helper('savedquote')->getStoreId())
                ->load($this->getProductId());
            $this->setProduct($product);
        }

        return $product;
    }

    /**
     * Check is product available for sale
     *
     * @return bool
     */
    public function canBePurchased()
    {
        $no = SDM_Catalog_Model_Attribute_Source_Allowcheckout::VALUE_NO;
        if ((int)$this->getProduct()->getData('allow_checkout') === $no) {
            return false;
        }

        $product = $this->getProduct();
        $qty = $product->getStockItem()->getQty();
        $qtyWanted = $this->getQty();

        if ($qtyWanted > $qty && !$product->getData('allow_checkout_backorder')) {
            return false;
        }
        
        return true;
    }

    /**
     * Returns the saved quote
     *
     * @return SDM_SavedQuote_Model_Savedquote
     */
    public function getSavedQuote()
    {
        return Mage::getModel('savedquote/savedquote')->load($this->getSavedQuoteId());
    }

    /**
     * Returns the qty shipping for a particular date, provided in a YYYY-MM format
     *
     * @param  string $date
     * @return int
     */
    public function getDateShipQty($date)
    {
        $shipData = Mage::helper('core')->jsonDecode($this->getPreOrderShippingDates());
        $qty = isset($shipData[$date]) ? $shipData[$date] : 0;
        return !empty($qty) && $qty > 0 ? $qty : 0;
    }
}
