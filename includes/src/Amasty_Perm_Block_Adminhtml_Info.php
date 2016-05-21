<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */
class Amasty_Perm_Block_Adminhtml_Info extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/amperm/info.phtml');
    }

    public function getDealers()
    {
        return Mage::helper('amperm')->getSalesPersonList();
    }

    public function getAssignedDealer($orderId)
    {
        $permissionManager = Mage::getModel('amperm/perm');
        return  intVal($permissionManager->getUserByOrder($orderId));
    }
    
    public function getCurrentDealer()
    {
        return Mage::helper('amperm')->getCurrentSalesPersonId();
    }        

    public function getMessages($orderId)
    {
        return Mage::getModel('amperm/message')
            ->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->setOrder('created_at','desc');
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('adminhtml/ampermassign/assign');
    }
}