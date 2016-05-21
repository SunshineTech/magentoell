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
 * @method Mirasvit_Helpdesk_Model_Resource_Ticket_Collection|Mirasvit_Helpdesk_Model_Ticket[] getCollection()
 * @method Mirasvit_Helpdesk_Model_Ticket load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Helpdesk_Model_Ticket setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Helpdesk_Model_Ticket setIsMassStatus(bool $flag)
 * @method Mirasvit_Helpdesk_Model_Resource_Ticket getResource()
 * @method string getName()
 * @method Mirasvit_Helpdesk_Model_Ticket setName(string $param)
 * @method int getDepartmentId()
 * @method Mirasvit_Helpdesk_Model_Ticket setDepartmentId(int $departmentId)
 * @method int getOrderId()
 * @method Mirasvit_Helpdesk_Model_Ticket setOrderId(int $id)
 * @method int getQuoteAddressId()
 * @method Mirasvit_Helpdesk_Model_Ticket setQuoteAddressId(int $id)
 * @method int getPriorityId()
 * @method Mirasvit_Helpdesk_Model_Ticket setPriorityId(int $priorityId)
 * @method int getStatusId()
 * @method Mirasvit_Helpdesk_Model_Ticket setStatusId(int $statusId)
 * @method int getEmailId()
 * @method Mirasvit_Helpdesk_Model_Ticket setEmailId(int $id)
 * @method int getUserId()
 * @method Mirasvit_Helpdesk_Model_Ticket setUserId(int $userId)
 * @method int getCustomerId()
 * @method Mirasvit_Helpdesk_Model_Ticket setCustomerId(int $id)
 * @method int getStoreId()
 * @method Mirasvit_Helpdesk_Model_Ticket setStoreId(int $userId)
 * @method string getLastReplyName()
 * @method Mirasvit_Helpdesk_Model_Ticket setLastReplyName(string $param)
 * @method string getThirdPartyEmail()
 * @method Mirasvit_Helpdesk_Model_Ticket setThirdPartyEmail(string $param)
 * @method bool getIsArchived()
 * @method Mirasvit_Helpdesk_Model_Ticket setIsArchived(bool $flag)
 * @method bool getIsSpam()
 * @method Mirasvit_Helpdesk_Model_Ticket setIsSpam(bool $flag)
 * @method int getReplyCnt()
 * @method Mirasvit_Helpdesk_Model_Ticket setReplyCnt(int $num)
 * @method string getFirstReplyAt()
 * @method Mirasvit_Helpdesk_Model_Ticket setFirstReplyAt(string $param)
 * @method string getLastReplyAt()
 * @method Mirasvit_Helpdesk_Model_Ticket setLastReplyAt(string $param)
 * @method string getFirstSolvedAt()
 * @method Mirasvit_Helpdesk_Model_Ticket setFirstSolvedAt(string $param)
 * @method string getCode()
 * @method Mirasvit_Helpdesk_Model_Ticket setCode(string $param)
 * @method string getExternalId()
 * @method Mirasvit_Helpdesk_Model_Ticket setExternalId(string $param)
 * @method string getCustomerName()
 * @method Mirasvit_Helpdesk_Model_Ticket setCustomerName(string $param)
 * @method string getCustomerEmail()
 * @method Mirasvit_Helpdesk_Model_Ticket setCustomerEmail(string $param)
 * @method string getCreatedAt()
 * @method Mirasvit_Helpdesk_Model_Ticket setCreatedAt(string $param)
 * @method string getUpdatedAt()
 * @method Mirasvit_Helpdesk_Model_Ticket setUpdatedAt(string $param)
 * @method string getChannel()
 * @method Mirasvit_Helpdesk_Model_Ticket setChannel(string $param)
 * @method array getChannelData()
 * @method Mirasvit_Helpdesk_Model_Ticket setChannelData(array $param)
 * @method Mirasvit_Helpdesk_Model_Ticket setCc(string $param)
 * @method Mirasvit_Helpdesk_Model_Ticket setBcc(string $param)
 * @method string getFpRemindEmail()
 * @method Mirasvit_Helpdesk_Model_Ticket setFpRemindEmail(string $param)
 * @method int getFpPriorityId()
 * @method Mirasvit_Helpdesk_Model_Ticket setFpPriorityId(int $id)
 * @method int getFpStatusId()
 * @method Mirasvit_Helpdesk_Model_Ticket setFpStatusId(int $id)
 * @method int getFpDepartmentId()
 * @method Mirasvit_Helpdesk_Model_Ticket setFpDepartmentId(int $id)
 * @method int getFpUserId()
 * @method Mirasvit_Helpdesk_Model_Ticket setFpUserId(int $id)
 * @method bool getFpIsRemind()
 * @method Mirasvit_Helpdesk_Model_Ticket setFpIsRemind(bool $flag)
 * @method int getMergedTicketId()
 * @method Mirasvit_Helpdesk_Model_Ticket setMergedTicketId(int $id)
 * @method int[] getTagIds()
 * @method Mirasvit_Helpdesk_Model_Ticket setTagIds(array $ids)
 * @method string getEmailSubjectPrefix()
 * @method int[] getTagIds()
 */
class Mirasvit_Helpdesk_Model_Ticket extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/ticket');
    }

    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    protected $_department = null;

    /**
     * @return bool|Mirasvit_Helpdesk_Model_Department
     */
    public function getDepartment()
    {
        if (!$this->getDepartmentId()) {
            return false;
        }
        if ($this->_department === null) {
            $this->_department = Mage::getModel('helpdesk/department')->load($this->getDepartmentId());
        }

        return $this->_department;
    }

    protected $_priority = null;

    /**
     * @return bool|Mirasvit_Helpdesk_Model_Priority
     */
    public function getPriority()
    {
        if (!$this->getPriorityId()) {
            return false;
        }
        if ($this->_priority === null) {
            $this->_priority = Mage::getModel('helpdesk/priority')->load($this->getPriorityId());
        }

        return $this->_priority;
    }

    protected $_status = null;

    /**
     * @return bool|Mirasvit_Helpdesk_Model_Status
     */
    public function getStatus()
    {
        if (!$this->getStatusId()) {
            return false;
        }
        if ($this->_status === null) {
            $this->_status = Mage::getModel('helpdesk/status')->load($this->getStatusId());
        }

        return $this->_status;
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

    protected $_store = null;

    /**
     * @return bool|Mage_Core_Model_Store
     */
    public function getStore()
    {
        if (!$this->getStoreId()) {
            return false;
        }
        if ($this->_store === null) {
            $this->_store = Mage::getModel('core/store')->load($this->getStoreId());
        }

        return $this->_store;
    }

    public function getCc()
    {
        $cc = $this->getData('cc');
        if ($cc) {
            $cc = explode(',', $cc);
            $cc = array_map('trim', $cc);

            return $cc;
        }

        return array();
    }

    public function getBcc()
    {
        $cc = $this->getData('bcc');
        if ($cc) {
            $cc = explode(',', $cc);
            $cc = array_map('trim', $cc);

            return $cc;
        }

        return array();
    }

    /************************/

    /**
     * @param string                                           $text
     * @param Mage_Customer_Model_Customer|Varien_Object|false $customer
     * @param Mage_Admin_Model_User|false                      $user
     * @param string                                           $triggeredBy
     * @param string                                           $messageType
     * @param bool|Mirasvit_Helpdesk_Model_Email               $email
     * @param bool|string                                      $bodyFormat
     *
     * @return Mirasvit_Helpdesk_Model_Message
     *
     * @throws Exception
     */
    public function addMessage($text, $customer, $user, $triggeredBy, $messageType = Mirasvit_Helpdesk_Model_Config::MESSAGE_PUBLIC, $email = false, $bodyFormat = false)
    {
        $message = Mage::getModel('helpdesk/message')
            ->setTicketId($this->getId())
            ->setType($messageType)
            ->setBody($text)
            ->setBodyFormat($bodyFormat)
            ->setTriggeredBy($triggeredBy)
            ;

        if ($triggeredBy == Mirasvit_Helpdesk_Model_Config::CUSTOMER) {
            $message->setCustomerId($customer->getId());
            $message->setCustomerName($customer->getName());
            /* @noinspection PhpUndefinedMethodInspection */
            $message->setCustomerEmail($customer->getEmail());
            $message->setIsRead(true);

            $this->setLastReplyName($customer->getName());
        } elseif ($triggeredBy == Mirasvit_Helpdesk_Model_Config::USER) {
            $message->setUserId($user->getId());
            if ($this->getOrigData('user_id') == $this->getData('user_id')) {
                if ($messageType != Mirasvit_Helpdesk_Model_Config::MESSAGE_INTERNAL) {
                    $this->setUserId($user->getId());
                    // In case of different departments of ticket and owner, correct department id
                    $departments = Mage::getModel('helpdesk/department')->getCollection();
                    $departments->addUserFilter($user->getId())
                                ->addFieldToFilter('is_active', true);
                    if ($departments->count()) {
                        $this->_department = null;
                        $this->setDepartmentId($departments->getFirstItem()->getId());
                    }
                }
            }
            $this->setLastReplyName($user->getName());
            if ($message->isThirdParty()) {
                $message->setThirdPartyEmail($this->getThirdPartyEmail());
            }
        } elseif ($triggeredBy == Mirasvit_Helpdesk_Model_Config::THIRD) {
            $message->setThirdPartyEmail($this->getThirdPartyEmail());
            if ($email) {
                $this->setLastReplyName($email->getSenderNameOrEmail());
                $message->setThirdPartyName($email->getSenderName());
            }
        }
        if ($email) {
            $message->setEmailId($email->getId());
        }
        //если тикет был закрыт, затем поступило сообщение от пользователя - мы его открываем
        if ($triggeredBy != Mirasvit_Helpdesk_Model_Config::USER) {
            if ($this->isClosed()) {
                $status = Mage::getModel('helpdesk/status')->loadByCode(Mirasvit_Helpdesk_Model_Config::STATUS_OPEN);
                $this->setStatusId($status->getId());
            }
            $this->setIsArchived(false);
        }
        $message->save();

        if ($email) {
            $email->setIsProcessed(true)
                  ->setAttachmentMessageId($message->getId())
                  ->save();
        } else {
            Mage::helper('helpdesk')->saveAttachments($message);
        }

        if (!$this->getIsSpam()) {
            if ($this->getReplyCnt() == 0) {
                Mage::helper('helpdesk/notification')->newTicket($this, $customer, $user, $triggeredBy, $messageType);
            } else {
                Mage::helper('helpdesk/notification')->newMessage($this, $customer, $user, $triggeredBy, $messageType);
            }
        }

        $this->setReplyCnt($this->getReplyCnt() + 1);
        if (!$this->getFirstReplyAt() && $user) {
            $this->setFirstReplyAt(Mage::getSingleton('core/date')->gmtDate());
        }
        $this->setLastReplyAt(Mage::getSingleton('core/date')->gmtDate());

        $this->save();
        Mage::helper('helpdesk/history')->addMessage($this, $text, $triggeredBy, array('customer' => $customer, 'user' => $user, 'email' => $email), $messageType);

        return $message;
    }

    protected function updateFields()
    {
        $config = Mage::getSingleton('helpdesk/config');
        if (!$this->getPriorityId()) {
            $this->setPriorityId($config->getDefaultPriority());
        }
        if (!$this->getStatusId()) {
            $this->setStatusId($config->getDefaultStatus());
        }
        if (!$this->getCode()) {
            $this->setCode(Mage::helper('helpdesk/string')->generateTicketCode());
        }
        if (!$this->getExternalId()) {
            $this->setExternalId(md5($this->getCode().Mage::helper('helpdesk/string')->generateRandNum(10)));
        }
        if ($this->getCustomerId() > 0) {
            $customer = Mage::getModel('customer/customer');
            $customer->load($this->getCustomerId());
            // мы не меняем емейл, т.к. человек мог прислать тикет не стого емейла по которому регился
            // может этот if уже не нужно???
            if (!$this->getCustomerEmail()) {
                /* @noinspection PhpUndefinedMethodInspection */
                $this->setCustomerEmail($customer->getEmail());
            }
            $this->setCustomerName($customer->getName());
        }
        if (!$this->getFirstSolvedAt() && $this->isClosed()) {
            $this->setFirstSolvedAt(Mage::getSingleton('core/date')->gmtDate());
        }
        if (in_array($this->getStatusId(), $config->getGeneralArchivedStatusList())) {
            $this->setIsArchived(true);
        }
    }

    protected function _beforeSave()
    {
        $this->updateFields();

        if ($this->getData('user_id') && ($this->getOrigData('user_id') != $this->getData('user_id'))) {
            Mage::helper('helpdesk/ruleevent')->newEvent(Mirasvit_Helpdesk_Model_Config::RULE_EVENT_TICKET_ASSIGNED, $this);
        }
        Mage::helper('helpdesk/ruleevent')->newEvent(Mirasvit_Helpdesk_Model_Config::RULE_EVENT_TICKET_UPDATED, $this);

        return parent::_beforeSave();
    }

    /*
     * Overridden superclass function. Deletes all emails linked with current ticket
     */
    protected function _beforeDelete()
    {
        $messages = Mage::getModel('helpdesk/message')->getCollection()
            ->addFieldToFilter('ticket_id', $this->getId());
        foreach ($messages as $message) {
            $message->delete();
        }

        return parent::_beforeDelete();
    }

    public function getUrl()
    {
        $url = Mage::getUrl('helpdesk/ticket/view', array('id' => $this->getId()));

        return $url;
    }

    public function getExternalUrl()
    {
        $url = Mage::getUrl('helpdesk/ticket/external', array('id' => $this->getExternalId(), '_store' => $this->getStoreId()));

        return $url;
    }

    public function getStopRemindUrl()
    {
        $url = Mage::getUrl('helpdesk/ticket/stopremind', array('id' => $this->getExternalId(), '_store' => $this->getStoreId()));

        return $url;
    }

    public function getBackendUrl()
    {
        $url = Mage::helper('adminhtml')->getUrl('helpdeskadmin/adminhtml_ticket/edit', array('id' => $this->getId()));

        return $url;
    }

    public function getMessages($includePrivate = false)
    {
        $collection = Mage::getModel('helpdesk/message')->getCollection();
        $collection
            ->addFieldToFilter('ticket_id', $this->getId())
            ->setOrder('created_at', 'desc');
        if (!$includePrivate) {
            $collection->addFieldToFilter('is_internal', 0);
            $collection->addFieldToFilter('type',
                    array(
                        array('eq' => ''),
                        array('eq' => Mirasvit_Helpdesk_Model_Config::MESSAGE_PUBLIC),
                        array('eq' => Mirasvit_Helpdesk_Model_Config::MESSAGE_PUBLIC_THIRD),
                    )
                );
        }

        return $collection;
    }

    public function getLastMessage()
    {
        $collection = Mage::getModel('helpdesk/message')->getCollection();
        $collection
            ->addFieldToFilter('ticket_id', $this->getId())
            ->setOrder('message_id', 'asc');

        return $collection->getLastItem();
    }

    public function getLastMessageHtmlText()
    {
        return $this->getLastMessage()->getBodyHtml();
    }

    public function getLastMessagePlainText()
    {
        return $this->getLastMessage()->getBodyPlain();
    }

    public function getCreatedAtFormated($format)
    {
        return Mage::helper('core')->formatDate($this->getCreatedAt(), $format).' '.Mage::helper('core')->formatTime($this->getCreatedAt(), $format);
    }

    public function getUpdatedAtFormated($format)
    {
        return Mage::helper('core')->formatDate($this->getUpdatedAt(), $format).' '.Mage::helper('core')->formatTime($this->getUpdatedAt(), $format);
    }

    public function open()
    {
        $status = Mage::getModel('helpdesk/status')->loadByCode(Mirasvit_Helpdesk_Model_Config::STATUS_OPEN);
        $this->setStatusId($status->getId())->save();
    }

    public function close()
    {
        $status = Mage::getModel('helpdesk/status')->loadByCode(Mirasvit_Helpdesk_Model_Config::STATUS_CLOSED);
        $this->setStatusId($status->getId())->save();
    }

    public function isClosed()
    {
        $status = Mage::getModel('helpdesk/status')->loadByCode(Mirasvit_Helpdesk_Model_Config::STATUS_CLOSED);
        if ($status->getId() == $this->getStatusId()) {
            return true;
        }

        return false;
    }

    public function initOwner($value, $prefix = false)
    {
        //set ticket user and department
        if ($value) {
            $owner = $value;
            $owner = explode('_', $owner);
            if ($prefix) {
                $prefix .= '_';
            }
            $this->setData($prefix.'department_id', (int) $owner[0]);
            $this->setData($prefix.'user_id', (int) $owner[1]);
        }

        return $this;
    }

    public function markAsSpam()
    {
        $this->setIsSpam(true)->save();
    }

    public function markAsNotSpam()
    {
        $this->setIsSpam(false)->save();
        if ($emailId = $this->getEmailId()) {
            $email = Mage::getModel('helpdesk/email')->load($emailId);
            $email->setPatternId(0)->save();
        }
    }

    protected $_customer = null;

    /**
     * @return bool|Mage_Customer_Model_Customer|Varien_Object
     */
    public function getCustomer()
    {
        if ($this->_customer === null) {
            if ($this->getCustomerId()) {
                $this->_customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
            } elseif ($this->getCustomerEmail()) {
                $this->_customer = new Varien_Object(array(
                    'name' => $this->getCustomerName(),
                    'email' => $this->getCustomerEmail(),
                ));
            } else {
                $this->_customer = false;
            }
        }

        return $this->_customer;
    }

    protected $_order = null;

    /**
     * @return bool|Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (!$this->getOrderId()) {
            return false;
        }
        if ($this->_order === null) {
            $this->_order = Mage::getModel('sales/order')->load($this->getOrderId());
        }

        return $this->_order;
    }

    public function getEmailSubject($subject = '')
    {
        if ($this->getEmailSubjectPrefix()) {
            $subject = $this->getEmailSubjectPrefix().$subject;
        }

        return Mage::helper('helpdesk/email')->getEmailSubject($this, $subject);
    }

    public function getHiddenCodeHtml()
    {
        if (!Mage::getSingleton('helpdesk/config')->getNotificationIsShowCode()) {
            return Mage::helper('helpdesk/email')->getHiddenCode($this->getCode());
        }
    }

    public function getHistoryHtml()
    {
        return Mage::helper('helpdesk')->getHistoryHtml($this);
    }

    public function getUserName()
    {
        if ($this->getUser()) {
            return $this->getUser()->getName();
        }
    }

    public function getTags()
    {
        $tags = array(0);
        if (is_array($this->getTagIds())) {
            $tags = array_merge($tags, $this->getTagIds());
        }
        $collection = Mage::getModel('helpdesk/tag')->getCollection()
                        ->addFieldToFilter('tag_id', $tags);

        return $collection;
    }

    public function loadTagIds()
    {
        if ($this->getData('tag_ids') === null) {
            $this->getResource()->loadTagIds($this);
        }
    }

    public function hasCustomer()
    {
        return $this->getCustomerId() > 0 || $this->getQuoteAddressId() > 0;
    }

    public function initFromOrder($orderId)
    {
        $this->setOrderId($orderId);
        $order = $this->getOrder();
        $address = $order->getShippingAddress();

        $this->setQuoteAddressId($address->getId());
        $this->setCustomerId($order->getCustomerId());
        $this->setStoreId($order->getStoreId());

        if ($this->getCustomerId()) {
            /* @noinspection PhpUndefinedMethodInspection */
            $this->setCustomerEmail($this->getCustomer()->getEmail());
        } elseif ($order->getCustomerEmail()) {
            $this->setCustomerEmail($order->getCustomerEmail());
        } else {
            $this->setCustomerEmail($address->getEmail());
        }

        return $this;
    }

    public function isThirdPartyPublic()
    {
        foreach ($this->getMessages(true) as $message) {
            if ($message->getType() == Mirasvit_Helpdesk_Model_Config::MESSAGE_PUBLIC_THIRD) {
                return true;
            }
            if ($message->getType() == Mirasvit_Helpdesk_Model_Config::MESSAGE_INTERNAL_THIRD) {
                return false;
            }
        }

        return true;
    }

    /************************/
}
