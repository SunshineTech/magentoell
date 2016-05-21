<?php
/**
 * Separation Degrees Media
 *
 * Ellison's Mage_Sales customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Sales
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Sales_Model_Order_Ax class
 */
class SDM_Sales_Model_Order_Ax extends Mage_Core_Model_Abstract
{
    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sdm_sales/order_ax');
    }

    /**
     * Returns the order's invoice AX account ID
     *
     * Note: Deprecated
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return int
     */
    // public function getInvoiceAccountId($order)
    // {
    //     return $this->_getResourceModel()->getInvoiceAccountId($order);
    // }

    /**
     * Load entity by attribute
     *
     * @param Mage_Eav_Model_Entity_Attribute_Interface|integer|string|array $attribute
     * @param null|string|array                                              $value
     * @param string                                                         $additionalAttributes
     *
     * @return bool|Mage_Catalog_Model_Abstract
     */
    public function loadByAttribute($attribute, $value, $additionalAttributes = '*')
    {
        $collection = $this->getResourceCollection()
            ->addFieldToSelect($additionalAttributes)
            ->addFieldToFilter($attribute, $value)
            ->setPageSize(1)
            ->setCurPage(1);

        foreach ($collection as $object) {
            return $object;
        }

        return false;
    }
}
