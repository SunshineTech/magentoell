<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */
class Amasty_Perm_Block_Description extends Mage_Core_Block_Template
{    
	public function getDealer()
	{
		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			
			$placement = $this->getPlacement();
			if (Mage::getStoreConfig('amperm/frontend/description_' . $placement) == 0) {
				return; 
			}
			
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getEntityId();
            $dealerId = Mage::getModel('amperm/perm')->getResource()->getUserByCustomer($customerId);
            $dealer = Mage::getModel('admin/user')->load($dealerId);
            return $dealer;
        }
	}
}

