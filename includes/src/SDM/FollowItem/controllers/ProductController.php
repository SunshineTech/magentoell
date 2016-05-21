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
 * SDM_FollowItem_ProductController class
 */
class SDM_FollowItem_ProductController extends Mage_Core_Controller_Front_Action
{
    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->followAction();
    }
    /**
     * Follow action
     *
     * @return void
     */
    public function followAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');

        if (!$this->_validateFormKey()) {
            $this->_standardError();
            return;
        }
        $productId = (int)$this->getRequest()->getParam('product');
        $product = Mage::getModel('catalog/product')->load($productId);
        $followText = $this->getRequest()->getParam('followtext');
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $response = array(
                'status' => 'error',
                'message' => $this->__('You must be logged in to follow items.'),
                'link'   => Mage::helper('followitem')->getProductFollowLinkHtml($product, $followText)
            );
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            return;
        }

        $productId = (int)$this->getRequest()->getParam('product');
        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId()) {
            $this->_standardError();
            return;
        }

        $follow = Mage::helper('followitem')->getFollowModel($product, 'product');
        if ($follow === null) {
            $follow = Mage::getModel('followitem/follow')
                ->setBaseData()
                ->setEntityId($productId)
                ->save();
            $response = array(
                'status' => 'success',
                'link'   => Mage::helper('followitem')->getProductFollowLinkHtml($product, $followText)
            );
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            return;
        } else {
            $this->_standardError();
        }
    }

    /**
     * Unfollow action
     *
     * @return void
     */
    public function unfollowAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');

        if (!$this->_validateFormKey()) {
            $this->_standardError();
            return;
        }

        $followText = $this->getRequest()->getParam('followtext');
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $response = array(
                'status' => 'error',
                'message' => $this->__('You must be logged in to follow items.')
            );
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            return;
        }

        $productId = (int)$this->getRequest()->getParam('product');
        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId()) {
            Mage::helper('followitem')->getFollowModel($productId, 'product')->delete();
            $this->_standardError();
            return;
        }

        Mage::helper('followitem')->getFollowModel($product, 'product')->delete();
        $response = array(
            'status' => 'success',
            'link'   => Mage::helper('followitem')->getProductFollowLinkHtml($product, $followText)
        );
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }

    /**
     * Send error
     *
     * @return void
     */
    protected function _standardError()
    {
        $product = (int)$this->getRequest()->getParam('product');
        $followText = $this->getRequest()->getParam('followtext');
        $response = array(
            'status' => 'error',
            'link'   => Mage::helper('followitem')->getProductFollowLinkHtml($product, $followText),
            'message' => $this->__('An error has occured. Please refresh the page and try again.')
        );
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }
}
