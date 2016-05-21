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
 * SDM_Sales_Block_Adminhtml_Sales_Order_View class
 */
class SDM_Sales_Block_Adminhtml_Sales_Order_View
    extends Mage_Adminhtml_Block_Sales_Order_View
{
    /**
     * Return the admin order page title including saved quote number, if available
     *
     * @return str
     */
    public function getHeaderText()
    {
        if ($_extOrderId = $this->getOrder()->getExtOrderId()) {
            $_extOrderId = '[' . $_extOrderId . '] ';
        } else {
            $_extOrderId = '';
        }

        if ($this->getOrder()->getSavedQuoteId()) {
            $savedQuoteId = ' | Converted from Saved Quote #' . $this->getOrder()->getSavedQuoteId();
        } else {
            $savedQuoteId = '';
        }

        return Mage::helper('sales')->__(
            'Order # %s %s | %s %s',
            $this->getOrder()->getRealOrderId(),
            $_extOrderId,
            $this->formatDate($this->getOrder()->getCreatedAtDate(), 'medium', true),
            $savedQuoteId
        );
    }
}
