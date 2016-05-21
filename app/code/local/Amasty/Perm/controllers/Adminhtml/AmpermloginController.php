<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */
class Amasty_Perm_Adminhtml_AmpermloginController extends Mage_Adminhtml_Controller_Action
{
    public function loginAction()
    {
        $id = $this->getRequest()->getParam('customer_id');
        $permKey = $this->getRequest()->getParam('perm_key');
        $websiteId = $this->getRequest()->getParam('website_id');

        $customer = Mage::getModel('customer/customer')->load($id);

        if (!$websiteId) {
            $websiteId = $customer->getWebsiteId();
        }

        $key = $customer->getPasswordHash();

        if (($permKey !== md5($id . $key)) || !$customer->getId()) {
            return $this->_redirect('admin/');
        }

        $hash  = md5(uniqid(mt_rand(), true));
        $login = Mage::getModel('amperm/login')
            ->setLoginHash($hash)
            ->setCustomerId($id)
            ->save();

        if ($websiteId != Mage::app()->getWebsite()->getId()) {
            $website = Mage::app()->getWebsite($websiteId);
            $url = $website->getDefaultStore()->getBaseUrl();
            if (false === strpos($url, '/index.php/')) {
                $url .= 'index.php/ampermfront/index/index/id/' . $hash;
            } else {
                $url .= 'ampermfront/index/index/id/' . $hash;
            }

            return $this->_redirectUrl($url);
        }

        return $this->_redirect('ampermfront/', array(
            'id'     => $hash,
            '_store' => $customer->getStoreId(),
        ));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/manage/login_as_customer');
    }

    public function indexAction()
    {
        $this->loadLayout()->_setActiveMenu('customer/manage');
        $this->_addContent($this->getLayout()->createBlock('amperm/adminhtml_websites'));
        $this->renderLayout();
    }
}