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
 * SDM_Sales_Helper_Adminhtml_Sales class
 */
class SDM_Sales_Helper_Adminhtml_Sales extends Mage_Adminhtml_Helper_Sales
{
    /**
     * Get "double" prices html (block with base and place currency)
     *
     * Modifies how UK Euro totals are displayed for the currency customization
     *
     * @param Varien_Object $dataObject
     * @param float         $basePrice
     * @param float         $price
     * @param bool          $strong
     * @param string        $separator
     *
     * @return string
     */
    public function displayPrices($dataObject, $basePrice, $price, $strong = false, $separator = '<br/>')
    {
        $order = false;
        if ($dataObject instanceof Mage_Sales_Model_Order) {
            $order = $dataObject;
        } else {
            $order = $dataObject->getOrder();
        }

        if ($order && $order->isCurrencyDifferent()) {
            $res = '';
            $storeCode = Mage::getModel('core/store')->load($order->getStoreId())->getCode();

            if ($storeCode === SDM_Core_Helper_Data::STORE_CODE_UK_EU) {
                $res .= $order->formatPrice($price);
            } else {
                $res .= '<strong>';
                $res .= $order->formatBasePrice($basePrice);
                $res .= '</strong>'.$separator;
                $res .= '['.$order->formatPrice($price).']';
            }
        } elseif ($order) {
            $res = $order->formatPrice($price);
            if ($strong) {
                $res = '<strong>'.$res.'</strong>';
            }
        } else {
            $res = Mage::app()->getStore()->formatPrice($price);
            if ($strong) {
                $res = '<strong>'.$res.'</strong>';
            }
        }
        return $res;
    }
}
