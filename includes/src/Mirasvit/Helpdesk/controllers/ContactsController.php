<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Help Desk MX
 * @version   1.1.3
 * @build     1578
 * @copyright Copyright (C) 2015 Mirasvit (http://mirasvit.com/)
 */



/** @noinspection PhpIncludeInspection */
require Mage::getBaseDir('app').'/code/core/Mage/Contacts/controllers/IndexController.php';
class Mirasvit_Helpdesk_ContactsController extends Mage_Contacts_IndexController
{
    public function indexAction()
    {
        if (Mage::getSingleton('helpdesk/config')->getGeneralContactUsIsActive()) {
            if (Mage::getSingleton('customer/session')->getCustomer()->getId()) {
                $this->_redirect('helpdesk/ticket');

                return;
            }
        }
        // $this->loadLayout();
        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();
        $update->addHandle('contacts_index_index');
        $this->loadLayoutUpdates();
        $this->generateLayoutXml();
        $this->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;

        $this->getLayout()->getBlock('contactForm')
            ->setFormAction(Mage::getUrl('*/*/post'));

        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        $this->renderLayout();
    }
}
