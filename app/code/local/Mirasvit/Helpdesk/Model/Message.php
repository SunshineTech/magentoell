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



/**
 * @method Mirasvit_Helpdesk_Model_Resource_Message_Collection|Mirasvit_Helpdesk_Model_Message[] getCollection()
 * @method Mirasvit_Helpdesk_Model_Message load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Helpdesk_Model_Message setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Helpdesk_Model_Message setIsMassStatus(bool $flag)
 * @method Mirasvit_Helpdesk_Model_Resource_Message getResource()
 * @method int getTicketId()
 * @method Mirasvit_Helpdesk_Model_Message setTicketId(int $ticketId)
 * @method int getUserId()
 * @method Mirasvit_Helpdesk_Model_Message setUserId(int $userId)
 * @method string getType()
 * @method Mirasvit_Helpdesk_Model_Message setType(string $type)
 * @method string getBody()
 * @method Mirasvit_Helpdesk_Model_Message setBody(string $body)
 * @method string getBodyFormat()
 * @method Mirasvit_Helpdesk_Model_Message setBodyFormat(string $format)
 * @method Mirasvit_Helpdesk_Model_Message setTriggeredBy(string $by)
 * @method int getCustomerId()
 * @method Mirasvit_Helpdesk_Model_Message setCustomerId(int $id)
 * @method string getCustomerName()
 * @method Mirasvit_Helpdesk_Model_Message setCustomerName(string $name)
 * @method string getCustomerEmail()
 * @method Mirasvit_Helpdesk_Model_Message setCustomerEmail(string $email)
 * @method bool getIsRead()
 * @method Mirasvit_Helpdesk_Model_Message setIsRead(boolean $flag)
 * @method string getThirdPartyEmail()
 * @method Mirasvit_Helpdesk_Model_Message setThirdPartyEmail(string $email)
 * @method string getThirdPartyName()
 * @method Mirasvit_Helpdesk_Model_Message setThirdPartyName(string $name)
 * @method int getEmailId()
 * @method Mirasvit_Helpdesk_Model_Message setEmailId(int $id)
 * @method string getUserName()
 * @method Mirasvit_Helpdesk_Model_Message setUserName(string $name)
 * @method string getUid()
 * @method Mirasvit_Helpdesk_Model_Message setUid(string $param)
 * @method string getCreatedAt()
 * @method Mirasvit_Helpdesk_Model_Message setCreatedAt(string $param)
 * @method string getUpdatedAt()
 * @method Mirasvit_Helpdesk_Model_Message setUpdatedAt(string $param)
 */
class Mirasvit_Helpdesk_Model_Message extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/message');
    }

    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    protected $_ticket = null;

    /**
     * @return bool|Mirasvit_Helpdesk_Model_Ticket
     */
    public function getTicket()
    {
        if (!$this->getTicketId()) {
            return false;
        }
        if ($this->_ticket === null) {
            $this->_ticket = Mage::getModel('helpdesk/ticket')->load($this->getTicketId());
        }

        return $this->_ticket;
    }

    protected $_user = null;

    /**
     * @return bool|Mage_Admin_Model_User
     */
    public function getUser()
    {
        if (!$this->getUserId()) {
            return false;
        }
        if ($this->_user === null) {
            $this->_user = Mage::getModel('admin/user')->load($this->getUserId());
        }

        return $this->_user;
    }

    /************************/

    public function getAttachments()
    {
        return Mage::getModel('helpdesk/attachment')->getCollection()
            ->addFieldToFilter('message_id', $this->getId());
    }

    public function getFrontendUserName()
    {
        if (Mage::getSingleton('helpdesk/config')->getGeneralSignTicketBy() == Mirasvit_Helpdesk_Model_Config::SIGN_TICKET_BY_DEPARTMENT) {
            $departments = Mage::getModel('helpdesk/department')->getCollection()
                           ->addUserFilter($this->getUserId());
            if ($departments->count()) {
                return $departments->getFirstItem()->getName();
            } else {
                return $this->getTicket()->getDepartment()->getName();
            }
        } else {
            return $this->getUserName();
        }
    }

    public function _beforeSave()
    {
        parent::_beforeSave();
        if (!$this->getUid()) {
            $uid = md5(
                Mage::getSingleton('core/date')->gmtDate().
                Mage::helper('mstcore/string')->generateRandHeavy(100));
            $this->setUid($uid);
        }
    }

    public function _beforeDelete()
    {
        $attachments = Mage::getModel('helpdesk/attachment')->getCollection()
            ->addFieldToFilter('message_id', $this->getId());
        foreach ($attachments as $attachment) {
            $attachment->delete();
        }
        $emails = Mage::getModel('helpdesk/email')->getCollection()
            ->addFieldToFilter('message_id', $this->getId());
        foreach ($emails as $email) {
            $email->delete();
        }

        return parent::_beforeDelete();
    }

    public function isThirdParty()
    {
        return $this->getType() == Mirasvit_Helpdesk_Model_Config::MESSAGE_PUBLIC_THIRD
                    || $this->getType() == Mirasvit_Helpdesk_Model_Config::MESSAGE_INTERNAL_THIRD;
    }

    public function isInternal()
    {
        return $this->getType() == Mirasvit_Helpdesk_Model_Config::MESSAGE_INTERNAL;
    }

    /**
     * we need this method to support DB from old releases.
     *
     * @return string
     */
    public function getTriggeredBy()
    {
        if ($this->getData('triggered_by')) {
            return $this->getData('triggered_by');
        }
        if ($this->getUser()) {
            return Mirasvit_Helpdesk_Model_Config::USER;
        }

        return Mirasvit_Helpdesk_Model_Config::CUSTOMER;
    }

    /**
     * return body in HTML format.
     */
    public function getBodyHtml()
    {
        $body = $this->getBody();
        if (!$this->isBodyHtml()) {
            $body = Mage::helper('helpdesk/string')->convertToHtml($body);
        }

        return $body;
    }

    /**
     * return body in Plain text format.
     */
    public function getBodyPlain()
    {
        $body = $this->getBody();
        if ($this->isBodyHtml()) {
            $body = Mage::helper('helpdesk/string')->convertToPlain($body);
        }

        return $body;
    }

    /**
     * is body saved in DB in html?
     */
    public function isBodyHtml()
    {
        if ($this->getBodyFormat() == Mirasvit_Helpdesk_Model_Config::FORMAT_PLAIN) {
            return false;
        }
        if ($this->getBodyFormat() == Mirasvit_Helpdesk_Model_Config::FORMAT_HTML) {
            return true;
        }
        $tags = array('<div ', '<p ', 'href=', '</p>', '</div>', '</a>', '<br>', '</br>');
        foreach ($tags as $tag) {
            if (strpos($this->getBody(), $tag) !== false) {
                return true;
            }
        }

        return false;
    }

    /************************/
}
