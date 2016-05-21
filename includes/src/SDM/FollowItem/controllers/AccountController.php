<?php
/**
 * Separation Degrees One
 *
 * Allows customers to follow an item
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_FollowItem
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_FollowItem_AccountController class
 */
class SDM_FollowItem_AccountController extends Mage_Core_Controller_Front_Action
{
    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_redirect('*/*/list');
    }

    /**
     * List action
     *
     * @return void
     */
    public function listAction()
    {
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            return $this->_redirect('customer/account/login');
        }

        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Follow Item List'));

        // Highlight active tab
        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('followitem/account/list');
        }

        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl()); // for the "back" link
        }

        $this->renderLayout();
    }
}
