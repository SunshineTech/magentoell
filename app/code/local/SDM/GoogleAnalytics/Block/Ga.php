<?php
/**
 * Separation Degrees One
 *
 * Ellison's Mage_GoogleAnalytics customization
 *
 * @category  SDM
 * @package   SDM_GoogleAnalytics
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Main block for rendering GA/Universal Analytics
 */
class SDM_GoogleAnalytics_Block_Ga extends Mage_GoogleAnalytics_Block_Ga
{
    /**
     * Render information about specified orders and their items.
     *
     * Rewritten to include local currency in the tracking code.
     *
     * @return string
     */
    protected function _getOrdersTrackingCodeUniversal()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds));
        $result = array();
        $result[] = "ga('require', 'ecommerce')";
        foreach ($collection as $order) {
            $result[] = sprintf("ga('ecommerce:addTransaction', {
'id': '%s',
'affiliation': '%s',
'revenue': '%s',
'tax': '%s',
'shipping': '%s',
'currency': '%s'
});",
                $order->getIncrementId(),
                $this->jsQuoteEscape(Mage::app()->getStore()->getFrontendName()),
                $order->getBaseGrandTotal(),
                $order->getBaseTaxAmount(),
                $order->getBaseShippingAmount(),
                $order->getOrderCurrencyCode()
            );
            foreach ($order->getAllVisibleItems() as $item) {
                $result[] = sprintf("ga('ecommerce:addItem', {
'id': '%s',
'sku': '%s',
'name': '%s',
'category': '%s',
'price': '%s',
'quantity': '%s'
});",
                    $order->getIncrementId(),
                    $this->jsQuoteEscape($item->getSku()),
                    $this->jsQuoteEscape($item->getName()),
                    null, // there is no "category" defined for the order item
                    $item->getBasePrice(),
                    $item->getQtyOrdered()
                );
            }
            $result[] = "ga('ecommerce:send');";
        }

        return implode("\n", $result);
    }
}
