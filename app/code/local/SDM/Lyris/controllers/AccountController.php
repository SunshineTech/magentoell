<?php
/**
 * Separation Degrees One
 *
 * Lyris Newsletter Management
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Lyris
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Controller for newsletter sign up
 */
class SDM_Lyris_AccountController
    extends Mage_Core_Controller_Front_Action
{
    /**
     * Whenever subscription preferences are accessed, set a cookie to hide the
     * popup
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        Mage::helper('sdm_lyris')->setCookie(
            Mage::getSingleton('sdm_lyris/config_popup')->getConvertDays()
        );
    }

    /**
     * Process edit action, intial sign up
     *
     * @return void
     */
    public function editAction()
    {
        if (!Mage::getSingleton('sdm_lyris/config_account')->isActive()) {
            return $this->norouteAction();
        }
        if ($this->getRequest()->getParam('email')) {
            Mage::register('sdm_lyris_account_edit', true);
            $email = $this->getRequest()->getParam('email');
            $result = Mage::getSingleton('sdm_lyris/api_account')->loadByEmail($email);
            if ($result !== true) {
                if ($result->code != 200) {
                    Mage::getSingleton('core/session')->addError(
                        $this->__('Failed to communicate with newsletter API.  Please try again later.')
                    );
                } else {
                    $body = $result->body;
                    Mage::getSingleton('core/session')->addError(
                        sprintf('%s: %s', $this->__(ucfirst((string) $body->TYPE)), $this->__((string) $body->DATA))
                    );
                }
            }
        }
        $this->loadLayout()
            ->renderLayout();
    }

    /**
     * Process account save
     *
     * @return void
     */
    public function saveAction()
    {
        if (!Mage::getSingleton('sdm_lyris/config_account')->isActive()) {
            return $this->norouteAction();
        }
        $post = $this->getRequest()->getPost();
        Mage::getSingleton('core/session')->setLyrisAccount($post);
        if (isset($post['edit'])) {
            return $this->_update($post);
        } elseif (isset($post['unsubscribe'])) {
            return $this->_unsubscribe($post);
        }
        $this->_create($post);
    }

    /**
     * Unsubscribe
     *
     * @return void
     */
    public function unsubscribeAction()
    {
        $this->loadLayout()
            ->renderLayout();
    }

    /**
     * Create subscription
     *
     * @param array $post
     *
     * @return void
     */
    protected function _create(array $post)
    {
        $result = Mage::getModel('sdm_lyris/api_account')
            ->create($post);
        if ($result->code != 200) {
            Mage::getSingleton('core/session')->addError(
                $this->__('Failed to communicate with newsletter API.  Please try again later.')
            );
        } else {
            $body = $result->body;
            if ($body->TYPE == 'success') {
                Mage::getSingleton('core/session')->addSuccess(
                    $this->__('Your have been successfully subscribed!')
                );
            } else {
                Mage::getSingleton('core/session')->addError(
                    sprintf('%s: %s', $this->__(ucfirst((string) $body->TYPE)), $this->__((string) $body->DATA))
                );
            }
        }
        $this->_redirect('newsletter/account/edit', array('email' => $post['email']));
    }

    /**
     * Update subscription
     *
     * @param array $post
     *
     * @return void
     */
    protected function _update(array $post)
    {
        $result = Mage::getModel('sdm_lyris/api_account')
            ->update($post);
        if ($result->code != 200) {
            Mage::getSingleton('core/session')->addError(
                $this->__('Failed to communicate with newsletter API.  Please try again later.')
            );
        } else {
            $body = $result->body;
            if ($body->TYPE == 'success') {
                Mage::getSingleton('core/session')->addSuccess(
                    $this->__('Your settings have been successfully updated!')
                );
            } else {
                Mage::getSingleton('core/session')->addError(
                    sprintf('%s: %s', $this->__(ucfirst((string) $body->TYPE)), $this->__((string) $body->DATA))
                );
            }
        }
        $this->_redirect('newsletter/account/edit', array('email' => $post['email']));
    }

    /**
     * Unsubscribe subscription
     *
     * @param array $post
     *
     * @return void
     */
    protected function _unsubscribe(array $post)
    {
        $result = Mage::getModel('sdm_lyris/api_account')
            ->unsubscribe($post);
        if ($result->code != 200) {
            Mage::getSingleton('core/session')->addError(
                $this->__('Failed to communicate with newsletter API.  Please try again later.')
            );
        } else {
            $body = $result->body;
            if ($body->TYPE == 'success') {
                Mage::getSingleton('core/session')->addSuccess(
                    $this->__('You have been unsubscribed')
                );
            } else {
                Mage::getSingleton('core/session')->addError(
                    sprintf('%s: %s', $this->__(ucfirst((string) $body->TYPE)), $this->__((string) $body->DATA))
                );
            }
        }
        $this->_redirect('newsletter/account/unsubscribe');
    }
}
