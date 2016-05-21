<?php
/**
 * Separation Degrees One
 *
 * Ellison's negotiated product prices
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_NegotiatedProduct
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_NegotiatedProduct_Model_Negotiatedproduct class
 */
class SDM_NegotiatedProduct_Model_Negotiatedproduct extends Mage_Core_Model_Abstract
{
    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('negotiatedproduct/negotiatedproduct');
    }

    /**
     * Returns the negotiated price given SKU and customer ID
     *
     * @param array $options
     * @param str   $attributeToSelect Must be '*' or only one attribute
     * @see   Mage_Catalog_Model_Abstract::loadByAttribute()
     *
     * @return double|bool
     */
    public function loadByAttributes($options, $attributeToSelect = '*')
    {
        if (!isset($options['product_id']) || !isset($options['customer_id'])) {
            return false;
        }

        $collection = $this->getResourceCollection()
            ->addFieldToSelect($attributeToSelect)
            ->addFieldToFilter('product_id', $options['product_id'])
            ->addFieldToFilter('customer_id', $options['customer_id'])
            ->setCurPage(1)
            ->setPageSize(1);

        foreach ($collection as $object) {
            if ($attributeToSelect == '*') {
                return $object;
            } else {
                return $object->getData($attributeToSelect);
            }

        }
        return false;
    }

    /**
     * Returns the resource collection
     *
     * @return SDM_Customer_Model_Resource_Negotiatedproduct_Collection
     */
    public function getResourceCollection()
    {
        return Mage::getResourceModel('negotiatedproduct/negotiatedproduct_collection');
    }
}
