<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */
class Amasty_Perm_Adminhtml_AmpermassigncustomerController extends Mage_Adminhtml_Controller_Action
{
    public function massAssignAction()
    {
        if (Mage::getSingleton('admin/session')->isAllowed('customer/manage/assign_dealer')) {
            $customerIds = $this->getRequest()->getParam('customer');
            $dealerId    = $this->getRequest()->getParam('amperm_value');

            if (!is_array($customerIds)) {
                $this->_getSession()->addError($this->__('Please select customer(s)'));
                $this->_redirect('adminhtml/customer/index');
            }

            $errorFlag = false;
            if (!strlen($dealerId)) {
                $errorFlag = true;
            } else {
                $dealer = Mage::getModel('admin/user')->load($dealerId);
                if (!Mage::helper('amperm')->isSalesPerson($dealer)) {
                    $errorFlag = true;
                }
            }
            if ($errorFlag) {
                $this->_getSession()->addError($this->__('Please select Dealer'));
                $this->_redirect('adminhtml/customer/index');
            }

            try {
                Mage::getModel('amperm/perm')->massAssignCustomers($dealerId, $customerIds);
                $message = count($customerIds) > 1 ? '%d customers have been successfully assigned to dealer %s %s' : '%d customer have been successfully assigned to dealer %s %s';
                $this->_getSession()->addSuccess($this->__(
                    $message,
                    count($customerIds), $dealer->getFirstname(), $dealer->getLastname()
                ));
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Error: %s', $e->getMessage()));
            }
        } else {
            $this->_getSession()->addError($this->__('Access denied.'));
        }
        $this->_redirect('adminhtml/customer/index');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/manage/assign_dealer');
    }
}