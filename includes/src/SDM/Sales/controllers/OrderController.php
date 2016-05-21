<?php
/**
 * Separation Degrees One
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Sales
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

require_once Mage::getModuleDir('controllers', 'Mage_Sales') . DS . 'OrderController.php';

/**
 * SDM_Sales_OrderController class
 */
class SDM_Sales_OrderController extends Mage_Sales_OrderController
{
    /**
     * Action for reorder
     *
     * @return this
     */
    public function reorderAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }
        $order = Mage::registry('current_order');
        Mage::getSingleton('checkout/cart')->truncate()->save();
        $this->_redirect(
            'sales/order/reorderplace',
            array('_query' => 'order_id='.$order->getId())
        );
    }

    /**
     * Action for reorder
     *
     * @return this
     */
    public function reorderplaceAction()
    {
        parent::reorderAction();
    }
}
