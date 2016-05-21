<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */
class Amasty_Perm_Adminhtml_AmpermassignController extends Mage_Adminhtml_Controller_Action
{
    public function assignAction()
    {
        $data = $this->getRequest()->getParam('amperm');

        $msg = array();

        if ($data['old_dealer'] != $data['new_dealer']) {
            Mage::getModel('amperm/perm')->updateOrder($data['order_id'], $data['new_dealer']);
            $msg[] = Mage::helper('amperm')->__('Order has been successfully assigned');
        }

        if (Mage::getStoreConfig('amperm/messages/enabled')) {
            $oldEmail = Mage::getStoreConfig('amperm/messages/admin_email');
            if ($data['old_dealer']){ // not admin
                $dealer   = Mage::getModel('admin/user')->load($data['old_dealer']);
                $oldEmail = $dealer->getEmail();
            }

            $newEmail = Mage::getStoreConfig('amperm/messages/admin_email');
            if ($data['new_dealer']){ // not admin
                $dealer   = Mage::getModel('admin/user')->load($data['new_dealer']);
                $newEmail = $dealer->getEmail();
            }

            $translate = Mage::getSingleton('core/translate');
            $translate->setTranslateInline(false);

            $dealers = Mage::helper('amperm')->getSalesPersonList();
            if (isset($dealers[$data['old_dealer']])) {
                $fromName = $dealers[$data['old_dealer']];
            } else {
                $fromName = Mage::helper('amperm')->__('Admin');
            }
            if (isset($dealers[$data['new_dealer']])) {
                $toName = $dealers[$data['new_dealer']];
            } else {
                $toName = Mage::helper('amperm')->__('Admin');
            }

            $order = Mage::getModel('sales/order')->load($data['order_id']);
            $tpl = Mage::getModel('core/email_template');
            $tpl->setDesignConfig(array('area'=>'frontend'))
                ->sendTransactional(
                    Mage::getStoreConfig('amperm/messages/template'),
                    Mage::getStoreConfig('amperm/messages/identity'),
                    array($oldEmail, $newEmail),
                    '',
                    array(
                        'order_id'  => $data['order_id'],
                        'increment_id' => $order->getIncrementId(),
                        'comment'   => $data['txt'],
                        'newdealer' => $toName,
                        'olddealer' => $fromName,
                    )
                );
            $translate->setTranslateInline(true);
        }

        if ($data['old_dealer'] != $data['new_dealer']
            || $data['txt']) {
            $messageModel = Mage::getModel('amperm/message')
                ->setOrderId($data['order_id'])
                ->setFromId($data['old_dealer'])
                ->setToId($data['new_dealer'])
                ->setTxt($data['txt'])
                ->setCreatedAt(date('Y-m-d H:i:s'))
                ->setAuthorId(Mage::helper('amperm')->getCurrentSalesPersonId());
            $messageModel->save();
        }

        if ($data['txt']) {
            $msg[] = Mage::helper('amperm')->__('Comment has been successfully added');
        }

        if (!empty($msg)) {
            foreach ($msg as $message)
                Mage::getSingleton('adminhtml/session')->addSuccess($message);
        } else {
            $message = Mage::helper('amperm')->__('Please write comment and/or assign order to another dealer.');
            Mage::getSingleton('adminhtml/session')->addError($message);
        }

        $uid = Mage::helper('amperm')->getCurrentSalesPersonId();
        if ($uid
            && $data['old_dealer'] != $data['new_dealer']) {
            $this->_redirect('adminhtml/sales_order/index');
        } else {
            $this->_redirect('adminhtml/sales_order/view', array('order_id' => $data['order_id']));
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/assign_order');
    }
}