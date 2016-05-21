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

$statusId = Mage::getModel('sales/order_status')->getCollection()
    ->addFieldToFilter('status', 'preorder')
    ->getFirstItem()
    ->getId();

Mage::getModel('sales/order_status')->load($statusId)->delete();
