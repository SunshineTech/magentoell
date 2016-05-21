<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */
class Amasty_Perm_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $hash  = $this->getRequest()->getParam('id');
        $login = Mage::getModel('amperm/login')->load($hash, 'login_hash');
        $customerId = $login->getCustomerId();

        if ($customerId) {
            $login->delete();
            $session = Mage::getSingleton('customer/session');
            if (version_compare(Mage::getVersion(), '1.5.1.0', '>=')) {
                $session->renewSession();
            } else {
                $this->_renewSession($session);
            }
            $session->loginById($customerId);

            return $this->_redirect('customer/account/');
        }

        return $this->_redirect('');
    }
    
    protected function _renewSession($session)
    {
        $session->getCookie()->delete($session->getSessionName());
        session_regenerate_id(true);//$session->regenerateSessionId();

        $sessionHosts = $session->getSessionHosts();
        $currentCookieDomain = $session->getCookie()->getDomain();
        if (is_array($sessionHosts)) {
            foreach (array_keys($sessionHosts) as $host) {
                // Delete cookies with the same name for parent domains
                if (strpos($currentCookieDomain, $host) > 0) {
                    $session->getCookie()->delete($session->getSessionName(), null, $host);
                }
            }
        }

        return true;
    }
}