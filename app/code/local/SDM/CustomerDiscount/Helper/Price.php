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
 * SDM_CustomerDiscount_Helper_Price class
 */
class SDM_CustomerDiscount_Helper_Price extends SDM_Core_Helper_Data
{
    /**
     * A number that will never be smaller than any price
     *
     * @var int
     */
    const VERY_BIG_NUMBER = 999999;

    /**
     * Returns the promotional price set in the "Special" taxonomy
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return double
     */
    public function getPromoPrice($product)
    {
        $price = Mage::helper('taxonomy')->getPromoPrice(
            $product,
            Mage::app()->getWebsite()->getId()
        );

        return $price;
    }

    /**
     * Returns the negotiated product price, if available.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $customerId
     *
     * @return double
     */
    public function getNegotiatedPrice($product, $customerId = null)
    {
        // If not specified, get the current user in session
        if (is_null($customerId)) {
            $customerId = $this->_getCustomerId();
        }

        if ((string)$customerId === '0') {  // Non-registered customers
            return self::VERY_BIG_NUMBER;
        }

        $price = Mage::getModel('negotiatedproduct/negotiatedproduct')
            ->loadByAttributes(
                array(
                    'product_id' => $product->getId(),
                    'customer_id' => $customerId
                ),
                'price'
            );

        if ($price) {
            return $price;
        }

        return self::VERY_BIG_NUMBER;
    }

    /**
     * Calculates retailer discount price that is based on customer's group and
     * the product's discount category
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $customerId
     *
     * @precondition Customer belongs to the retailer website
     *
     * @return double
     */
    public function getRetailerPrice($product, $customerId = null)
    {
        // If not specified, get the current user in session
        if (is_null($customerId)) {
            $customerId = $this->_getCustomerId();
        }

        if ((string)$customerId === '0') {  // Non-registered customers
            return self::VERY_BIG_NUMBER;
        }

        $customerGroupId = $this->_getCustomerGroupId($customerId);
        // Calculate customer group-discounted price
        $groupPrice = $this->_getCustomerGroupDiscountedPrice($product, $customerGroupId);

        return $groupPrice;
    }

    /**
     * Returns the current session's customer ID
     *
     * @return int
     */
    protected function _getCustomerId()
    {
        $customerSession = Mage::getSingleton('customer/session');
        if ($customerSession->isLoggedIn()) {
            $customerId = $customerSession->getCustomerId();
        } else {
            $customerId = '0';
        }

        return $customerId;
    }

    /**
     * Returns the retailer discount pricing that is based on the customer group
     * and the product's discount category.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $customerGroupId
     *
     * @return double
     */
    protected function _getCustomerGroupDiscountedPrice($product, $customerGroupId)
    {
        $price = $product->getPrice();

        // Get the discount category ID (taxonomy item entity ID)
        $discountCategoryId = $product->getTagDiscountCategory();
        if (!$discountCategoryId) {
            $discountCategoryId = Mage::getModel('catalog/product')->load($product->getId())
                ->getTagDiscountCategory();
            if (!$discountCategoryId || $discountCategoryId == 0) {
                return self::VERY_BIG_NUMBER;
            }
        }

        // Get the discount percentage
        $select = $this->getConn()->select();
        $select->from($this->getTableName('customerdiscount/discountgroup'), array('amount'))
            ->where('category_id = ' . $discountCategoryId)
            ->where('customer_group_id = ' . $customerGroupId);
        $result = $this->getConn()->fetchCol($select);

        $discountPercentage = reset($result);
        if (!$discountPercentage || $discountPercentage < 0) {
            $discountPercentage = 0;
        }

        return round($price - ($price * $discountPercentage / 100), 2);
    }

    /**
     * Returns the customer group ID given a customer ID
     *
     * @param int $id Customer ID
     *
     * @return int
     */
    protected function _getCustomerGroupId($id)
    {
        $select = $this->getConn()->select();
        $select->from($this->getTableName('customer/entity'), array('group_id'))
            // ->where('entity_id = ?', $id);
            ->where('entity_id = ' . $id);
        $result = $this->getConn()->fetchCol($select);

        return reset($result);
    }
}
