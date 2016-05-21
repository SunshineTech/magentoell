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
 * @method Mirasvit_Helpdesk_Model_Resource_Satisfaction_Collection|Mirasvit_Helpdesk_Model_Satisfaction[] getCollection()
 * @method Mirasvit_Helpdesk_Model_Satisfaction load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Helpdesk_Model_Satisfaction setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Helpdesk_Model_Satisfaction setIsMassStatus(bool $flag)
 * @method Mirasvit_Helpdesk_Model_Resource_Satisfaction getResource()
 * @method int getMessageId()
 * @method Mirasvit_Helpdesk_Model_Satisfaction setMessageId(int $messageId)
 * @method int getTicketId()
 * @method Mirasvit_Helpdesk_Model_Satisfaction setTicketId(int $ticketId)
 * @method int getRate()
 * @method Mirasvit_Helpdesk_Model_Satisfaction setRate(int $rate)
 * @method int getCustomerId()
 * @method Mirasvit_Helpdesk_Model_Satisfaction setCustomerId(int $id)
 * @method int getUserId()
 * @method Mirasvit_Helpdesk_Model_Satisfaction setUserId(int $id)
 * @method int getStoreId()
 * @method Mirasvit_Helpdesk_Model_Satisfaction setStoreId(int $id)
 * @method string getComment()
 * @method Mirasvit_Helpdesk_Model_Satisfaction setComment(string $param)
 */
class Mirasvit_Helpdesk_Model_Satisfaction extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/satisfaction');
    }

    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    protected $_message = null;

    /**
     * @return bool|Mirasvit_Helpdesk_Model_Message
     */
    public function getMessage()
    {
        if (!$this->getMessageId()) {
            return false;
        }
        if ($this->_message === null) {
            $this->_message = Mage::getModel('helpdesk/message')->load($this->getMessageId());
        }

        return $this->_message;
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

    /************************/
}
