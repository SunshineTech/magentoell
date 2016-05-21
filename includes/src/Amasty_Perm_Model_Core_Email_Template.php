<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */
class Amasty_Perm_Model_Core_Email_Template extends Amasty_Perm_Model_Core_Email_Template_Pure
{
    public function sendTransactional($templateId, $sender, $email, $name, $vars = array(), $storeId = null)
    {
        if (isset($vars['order']) && !isset($vars['dealer'])) {
            $order = $vars['order'];
            $dealerId = Mage::getModel('amperm/perm')->getUserByOrder($order->getId());
            if ($dealerId) {
                $user = Mage::getModel('admin/user')->load($dealerId);
                $vars['dealer'] = $user;
            }
        }
        return parent::sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
    }
}