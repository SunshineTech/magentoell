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
 * @method Mirasvit_Helpdesk_Model_Resource_Email_Collection|Mirasvit_Helpdesk_Model_Email[] getCollection()
 * @method Mirasvit_Helpdesk_Model_Email load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Helpdesk_Model_Email setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Helpdesk_Model_Email setIsMassStatus(bool $flag)
 * @method Mirasvit_Helpdesk_Model_Resource_Email getResource()
 * @method string getSenderName()
 * @method Mirasvit_Helpdesk_Model_Email setSenderName(string $name)
 * @method bool getIsProcessed()
 * @method Mirasvit_Helpdesk_Model_Email setIsProcessed(bool $flag)
 * @method int getAttachmentMessageId()
 * @method Mirasvit_Helpdesk_Model_Email setAttachmentMessageId(int $id)
 * @method string getFromEmail()
 * @method Mirasvit_Helpdesk_Model_Email setFromEmail(string $email)
 * @method int getGatewayId()
 * @method Mirasvit_Helpdesk_Model_Email setGatewayId(int $id)
 * @method int getPatternId()
 * @method Mirasvit_Helpdesk_Model_Email setPatternId(int $id)
 * @method string getHeaders()
 * @method Mirasvit_Helpdesk_Model_Email setHeaders(string $param)
 * @method string getSubject()
 * @method Mirasvit_Helpdesk_Model_Email setSubject(string $param)
 * @method string getBody()
 * @method Mirasvit_Helpdesk_Model_Email setBody(string $param)
 * @method string getToEmail()
 * @method Mirasvit_Helpdesk_Model_Email setToEmail(string $param)
 * @method string getCc()
 * @method Mirasvit_Helpdesk_Model_Email setCc(string $param)
 * @method string getFormat()
 * @method Mirasvit_Helpdesk_Model_Email setFormat(string $param)
 * @method int getMessageId()
 * @method Mirasvit_Helpdesk_Model_Email setMessageId(int $param)
 */
class Mirasvit_Helpdesk_Model_Email extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/email');
    }

    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    /************************/

    public function getAttachments()
    {
        return Mage::getModel('helpdesk/attachment')->getCollection()
            ->addFieldToFilter('email_id', $this->getId());
    }

    public function getSenderNameOrEmail()
    {
        if ($this->getSenderName()) {
            return $this->getSenderName();
        }

        return $this->getFromEmail();
    }

    protected $_gateway = null;
    public function getGateway()
    {
        if (!$this->getGatewayId()) {
            return false;
        }
        if ($this->_gateway === null) {
            $this->_gateway = Mage::getModel('helpdesk/gateway')->load($this->getGatewayId());
        }

        return $this->_gateway;
    }

    /*
     * Deletes all attachments linked with current email
     */
    public function _beforeDelete()
    {
        $attachments = Mage::getModel('helpdesk/attachment')->getCollection()
            ->addFieldToFilter('email_id', $this->getId());
        foreach ($attachments as $attachment) {
            $attachment->delete();
        }

        return parent::_beforeDelete();
    }
}
