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
 * SDM_Sales_Model_Resource_Order_Ax class
 */
class SDM_Sales_Model_Resource_Order_Ax extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize table and PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sdm_sales/order_ax', 'id');
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
    //     $adapter = $this->_getReadAdapter();

    //     $select = $adapter->select()
    //         ->from(
    //             array('ax' => $this->getTable('sdm_sales/order_ax')),
    //             array('number')
    //             )
    //         ->where('ax.parent_id = ?', $order->getId())
    //         ->limit(1);

    //     $result = $adapter->fetchCol($select);

    //     if (!empty($result)) {
    //         return reset($result);
    //     }
    // }
}
