<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */
class Amasty_Perm_Block_Select extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/amperm/select.phtml');
    }

    public function getDealerId()
    {
        $dealerId = 0;
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getEntityId();
            $dealerId = Mage::getModel('amperm/perm')->getResource()->getUserByCustomer($customerId);
        }
        return $dealerId;
    }
}

