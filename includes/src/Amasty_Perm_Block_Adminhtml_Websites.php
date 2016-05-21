<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */
class  Amasty_Perm_Block_Adminhtml_Websites extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'amperm';
        $this->_controller = 'adminhtml_websites';

        $customerId = $this->getRequest()->getParam('customer_id');
        $customer = Mage::getModel('customer/customer')->load($customerId);

        $this->_headerText = Mage::helper('amperm')->__('Login as customer %s (click website to log into)', $customer->getFirstname() . ' ' . $customer->getLastname());

        parent::__construct();

        $this->_removeButton('add');
    }
}