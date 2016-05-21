<?php
/**
 * Separation Degrees One
 *
 * Pre Order Module
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_PreOrder
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * @var Mage_Core_Model_Resource_Setup
 */

$status = Mage::getModel('sales/order_status')->setData(array(
    'status' => 'preorder',
    'label'  => 'Pre-Order'
));
$status->save();

$status->assignState(Mage_Sales_Model_Order::STATE_PROCESSING);
