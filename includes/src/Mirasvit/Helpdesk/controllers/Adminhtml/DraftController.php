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



class Mirasvit_Helpdesk_Adminhtml_DraftController extends Mage_Adminhtml_Controller_Action
{
    public function updateAction()
    {
        $ticketId = (int) $this->getRequest()->getParam('ticket_id');
        $text = $this->getRequest()->getParam('text');
        if ($text == -1) {
            $text = false;
        }
        /* @noinspection PhpUndefinedMethodInspection */
        $userId = Mage::getSingleton('admin/session')->getUser()->getUserId();
        echo Mage::helper('helpdesk/draft')->getNoticeMessage($ticketId, $userId, $text);
        die;
    }

    protected function _isAllowed()
    {
        return true;
    }
}
