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

// Add new statuses
$this->updateStatus(
    SDM_Sales_Helper_Data::ORDER_STATUS_LABEL_PENDING,
    SDM_Sales_Helper_Data::ORDER_STATUS_CODE_PENDING,
    Mage_Sales_Model_Order::STATE_PROCESSING
);

$this->updateStatus(
    SDM_Sales_Helper_Data::ORDER_STATUS_LABEL_NEW,
    SDM_Sales_Helper_Data::ORDER_STATUS_CODE_NEW,
    Mage_Sales_Model_Order::STATE_PROCESSING
);

$this->updateStatus(
    SDM_Sales_Helper_Data::ORDER_STATUS_LABEL_OPEN,
    SDM_Sales_Helper_Data::ORDER_STATUS_CODE_OPEN,
    Mage_Sales_Model_Order::STATE_PROCESSING
);

$this->updateStatus(
    SDM_Sales_Helper_Data::ORDER_STATUS_LABEL_INPROCESS,
    SDM_Sales_Helper_Data::ORDER_STATUS_CODE_INPROCESS,
    Mage_Sales_Model_Order::STATE_PROCESSING,
    // Sets it to the default status for this state. Status is used when orders
    // have begin to have invoices and shipments created.
    true
);

// This one is native to Magento
// $this->updateStatus(
//     SDM_Sales_Helper_Data::ORDER_STATUS_LABEL_PROCESSING,
//     SDM_Sales_Helper_Data::ORDER_STATUS_CODE_PROCESSING,
//     Mage_Sales_Model_Order::STATE_PROCESSING
// );

$this->updateStatus(
    SDM_Sales_Helper_Data::ORDER_STATUS_LABEL_SHIPPED,
    'complete',
    Mage_Sales_Model_Order::STATE_COMPLETE,
    true
);
