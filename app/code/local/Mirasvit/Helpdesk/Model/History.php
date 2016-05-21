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
 * @method Mirasvit_Helpdesk_Model_Resource_History_Collection|Mirasvit_Helpdesk_Model_History[] getCollection()
 * @method Mirasvit_Helpdesk_Model_History load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Helpdesk_Model_History setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Helpdesk_Model_History setIsMassStatus(bool $flag)
 * @method Mirasvit_Helpdesk_Model_Resource_History getResource()
 * @method int getTicketId()
 * @method Mirasvit_Helpdesk_Model_History setTicketId(int $ticketId)
 * @method string getMessage()
 * @method Mirasvit_Helpdesk_Model_History setMessage(string $param)
 * @method string getTriggeredBy()
 * @method Mirasvit_Helpdesk_Model_History setTriggeredBy(string $param)
 * @method string getName()
 * @method Mirasvit_Helpdesk_Model_History setName(string $param)
 */
class Mirasvit_Helpdesk_Model_History extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/history');
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

    /************************/

    public function addMessage($text)
    {
        if (is_array($text)) {
            $text = implode("\n", $text);
        }
        $this->setMessage(trim($this->getMessage()."\n".$text));
        if ($this->getMessage()) {
            $this->save();
        };

        return $this;
    }

    /************************/
}
