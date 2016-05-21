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
 * SDM_Sales_Block_Adminhtml_Sales_Order_View_Items_Renderer_Default class
 */
class SDM_Sales_Block_Adminhtml_Sales_Order_View_Items_Renderer_Default
    extends Mage_Adminhtml_Block_Sales_Order_View_Items_Renderer_Default
{
    /**
     * Display base and regular prices with specified rounding precision
     *
     * Modifies UK Euro order price display
     *
     * @param float  $basePrice
     * @param float  $price
     * @param int    $precision
     * @param bool   $strong
     * @param string $separator
     *
     * @return string
     */
    public function displayRoundedPrices($basePrice, $price, $precision = 2, $strong = false, $separator = '<br />')
    {
        if ($this->getOrder()->isCurrencyDifferent()) {
            $res = '';
            $storeCode = Mage::getModel('core/store')->load($this->getOrder()->getStoreId())->getCode();
            if ($storeCode === SDM_Core_Helper_Data::STORE_CODE_UK_EU) {
                $res.= $this->getOrder()->formatPricePrecision($price, $precision, false);
            } else {
                $res.= $this->getOrder()->formatBasePricePrecision($basePrice, $precision);
                $res.= $separator;
                $res.= $this->getOrder()->formatPricePrecision($price, $precision, true);
            }
        } else {
            $res = $this->getOrder()->formatPricePrecision($price, $precision);
            if ($strong) {
                $res = '<strong>'.$res.'</strong>';
            }
        }
        return $res;
    }
}
