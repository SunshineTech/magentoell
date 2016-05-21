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



class Mirasvit_Helpdesk_Helper_Process extends Varien_Object
{
    public function getConfig()
    {
        return Mage::getSingleton('helpdesk/config');
    }

    /**
     * creates ticket from frontend.
     *
     * @param array  $post
     * @param string $channel
     *
     * @return Mirasvit_Helpdesk_Model_Ticket
     */
    public function createFromPost($post, $channel)
    {
        $ticket = Mage::getModel('helpdesk/ticket');
        // если кастомер не был авторизирован, то ищем его
        $customer = Mage::helper('helpdesk/customer')->getCustomerByPost($post);
        /* @noinspection PhpUndefinedMethodInspection */
        $ticket->setCustomerId($customer->getId())
            ->setCustomerEmail($customer->getEmail())
            ->setCustomerName($customer->getName())
            ->setQuoteAddressId($customer->getQuoteAddressId())
            ->setCode(Mage::helper('helpdesk/string')->generateTicketCode())
            ->setName($post['name']);
            //->setDescription($this->getEnviromentDescription());

        if (isset($post['priority_id'])) {
            $ticket->setPriorityId((int) $post['priority_id']);
        }
        if (isset($post['department_id'])) {
            $ticket->setDepartmentId((int) $post['department_id']);
        } else {
            $ticket->setDepartmentId($this->getConfig()->getContactFormDefaultDepartment());
        }
        if (isset($post['order_id'])) {
            $ticket->setOrderId((int) $post['order_id']);
        }
        $ticket->setStoreId(Mage::app()->getStore()->getId());
        $ticket->setChannel($channel);
        if ($channel == Mirasvit_Helpdesk_Model_Config::CHANNEL_FEEDBACK_TAB) {
            /* @noinspection PhpUndefinedMethodInspection */
            $url = Mage::getSingleton('customer/session')->getFeedbackUrl();
            $ticket->setChannelData(array('url' => $url));
        }

        Mage::helper('helpdesk/field')->processPost($post, $ticket);
        $ticket->save();
        $body = $post['message'];
        // $body = Mage::helper('helpdesk/string')->parseBody($post['message'], Mirasvit_Helpdesk_Model_Config::FORMAT_PLAIN);
        $ticket->addMessage($body, $customer, false, Mirasvit_Helpdesk_Model_Config::CUSTOMER, Mirasvit_Helpdesk_Model_Config::MESSAGE_PUBLIC, false, Mirasvit_Helpdesk_Model_Config::FORMAT_PLAIN);
        Mage::helper('helpdesk/history')->changeTicket($ticket, Mirasvit_Helpdesk_Model_Config::CUSTOMER, array('customer' => $customer));

        return $ticket;
    }

    public function getEnviromentDescription()
    {
        return print_r($_SERVER, true);
    }

    public function createOrUpdateFromBackendPost($data, $user)
    {
        $ticket = Mage::getModel('helpdesk/ticket');
        if (isset($data['ticket_id']) && (int) $data['ticket_id'] > 0) {
            $ticket->load((int) $data['ticket_id']);
        }
        if (!Zend_Validate::is($data['customer_email'], 'EmailAddress')) {
            throw new Mage_Core_Exception('Invalid Customer Email');
        }
        if (!isset($data['customer_id']) || !$data['customer_id']) {
            if (!$ticket->getCustomerName()) {
                $data['customer_name'] = $data['customer_email'];
            }
        }
        if (isset($data['customer_id']) && strpos($data['customer_id'], 'address_') !== false) {
            $data['quote_address_id'] = (int) str_replace('address_', '', $data['customer_id']);
            $data['customer_id'] = null;
            if ($data['quote_address_id']) {
                $quoteAddress = Mage::getModel('sales/order_address');
                $quoteAddress->load($data['quote_address_id']);
                $data['customer_name'] = $quoteAddress->getName();
            }
        } else {
            $data['quote_address_id'] = null;
        }

        $ticket->addData($data);

        if ($data['allowCC'] == 'false') {
            $ticket->setCc('');
        }

        if ($data['allowBCC'] == 'false') {
            $ticket->setBcc('');
        }

        Mage::helper('helpdesk/tag')->setTags($ticket, $data['tags']);
        //set custom fields
        Mage::helper('helpdesk/field')->processPost($data, $ticket);
        //set ticket user and department
        if (isset($data['owner'])) {
            $ticket->initOwner($data['owner']);
        }
        if (isset($data['fp_owner'])) {
            $ticket->initOwner($data['fp_owner'], 'fp');
        }
        if ($data['fp_period_unit'] == 'custom') {
            $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
            Mage::helper('mstcore/date')->formatDateForSave($ticket, 'fp_execute_at', $format);
        } elseif ($data['fp_period_value']) {
            $ticket->setData('fp_execute_at', $this->createFpDate($data['fp_period_unit'], $data['fp_period_value']));
        }
        if (!$ticket->getId()) {
            $ticket->setChannel(Mirasvit_Helpdesk_Model_Config::CHANNEL_BACKEND);
        }

        $ticket->save();

        $bodyFormat = Mirasvit_Helpdesk_Model_Config::FORMAT_PLAIN;
        if ($this->getConfig()->getGeneralIsWysiwyg()) {
            $bodyFormat = Mirasvit_Helpdesk_Model_Config::FORMAT_HTML;
        }
        if (trim($data['reply']) || $_FILES['attachment']['name'][0] != '') {
            $ticket->addMessage($data['reply'], false, $user, Mirasvit_Helpdesk_Model_Config::USER, $data['reply_type'], false, $bodyFormat);
        }
        Mage::helper('helpdesk/history')->changeTicket($ticket, Mirasvit_Helpdesk_Model_Config::USER, array('user' => $user));
        Mage::helper('helpdesk/draft')->clearDraft($ticket);

        return $ticket;
    }

    public function createFpDate($unit, $value)
    {
        $timeshift = 0;
        switch ($unit) {
            case 'minutes':
                $timeshift = $value;
                break;
            case 'hours':
                $timeshift = $value * 60;
                break;
            case 'days':
                $timeshift = $value * 60 * 24;
                break;
            case 'weeks':
                $timeshift = $value * 60 * 24 * 7;
                break;
            case 'months':
                $timeshift = $value * 60 * 24 * 31;
                break;
        }
        $timeshift *= 60; //in seconds
        $time = strtotime(Mage::getSingleton('core/date')->gmtDate()) + $timeshift;
        $time = date('Y-m-d H:i:s', $time);

        return $time;
    }

    public function isDev()
    {
        return Mage::getSingleton('helpdesk/config')->getDeveloperIsActive();
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Email $email
     * @param string                        $code
     *
     * @return bool|Mirasvit_Helpdesk_Model_Ticket
     *
     * @throws Exception
     */
    public function processEmail($email, $code)
    {
        $ticket = false;
        $customer = false;
        $user = false;
        $triggeredBy = Mirasvit_Helpdesk_Model_Config::CUSTOMER;
        $messageType = Mirasvit_Helpdesk_Model_Config::MESSAGE_PUBLIC;

        if ($code) {
            //try to find customer for this email
            $tickets = Mage::getModel('helpdesk/ticket')->getCollection();
            $tickets->addFieldToFilter('code', $code)
                    ->addFieldToFilter('customer_email', $email->getFromEmail())
                    ;
            if ($tickets->count()) {
                $ticket = $tickets->getFirstItem();
            } else {
                //try to find staff user for this email
                $users = Mage::getModel('admin/user')->getCollection()
                    ->addFieldToFilter('email', $email->getFromEmail())
                    ;

                if ($users->count()) {
                    $user = $users->getFirstItem();
                    $tickets = Mage::getModel('helpdesk/ticket')->getCollection()
                                ->addFieldToFilter('code', $code)
                                ;
                    if ($tickets->count()) {
                        $ticket = $tickets->getFirstItem();
                        $ticket->setUserId($user->getId());
                        $ticket->save();
                        $triggeredBy = Mirasvit_Helpdesk_Model_Config::USER;
                    } else {
                        $user = false; //@temp dva for testing
                    }
                } else { //third party
                    $tickets = Mage::getModel('helpdesk/ticket')->getCollection()
                                ->addFieldToFilter('code', $code)
                                ;
                    if ($tickets->count()) {
                        $ticket = $tickets->getFirstItem();
                        $triggeredBy = Mirasvit_Helpdesk_Model_Config::THIRD;
                        if ($ticket->isThirdPartyPublic()) {
                            $messageType = Mirasvit_Helpdesk_Model_Config::MESSAGE_PUBLIC_THIRD;
                        } else {
                            $messageType = Mirasvit_Helpdesk_Model_Config::MESSAGE_INTERNAL_THIRD;
                        }
                    }
                }
            }
        }

        if (!$user) {
            $customer = Mage::helper('helpdesk/customer')->getCustomerByEmail($email);
        }
        // create a new ticket
        if (!$ticket) {
            $ticket = Mage::getModel('helpdesk/ticket');
            if (!$code) {
                $ticket->setCode(Mage::helper('helpdesk/string')->generateTicketCode());
            } else {
                $ticket->setCode($code);//temporary for testing to fix @dva
            }
            $gateway = Mage::getModel('helpdesk/gateway')->load($email->getGatewayId());
            if ($gateway->getId()) {
                if ($gateway->getDepartmentId()) {
                    $ticket->setDepartmentId($gateway->getDepartmentId());
                } else { //if department was removed
                    $departments = Mage::getModel('helpdesk/department')->getCollection()
                                        ->addFieldToFilter('is_active', true);
                    if ($departments->count()) {
                        $department = $departments->getFirstItem();
                        $ticket->setDepartmentId($department->getId());
                    } else {
                        // throw new Exception("Helpdesk MX - Can't find any active department. Helpdesk can't fetch tickets correctly!");
                        Mage::log('Helpdesk MX - Can\'t find any active department. Helpdesk can\'t fetch tickets correctly!');
                    }
                }
                $ticket->setStoreId($gateway->getStoreId());
            }

            /* @noinspection PhpUndefinedMethodInspection */
            $ticket
                ->setName($email->getSubject())
                ->setCustomerName($customer->getName())
                ->setCustomerId($customer->getId())
                ->setQuoteAddressId($customer->getQuoteAddressId())
                ->setCustomerEmail($email->getFromEmail())
                ->setChannel(Mirasvit_Helpdesk_Model_Config::CHANNEL_EMAIL)
                ->setCc($email->getCc())
                ;

            $ticket->setEmailId($email->getId());
            $ticket->save();
            if ($pattern = $this->checkForSpamPattern($email)) {
                $ticket->markAsSpam($pattern);
                if ($email) {
                    $email->setPatternId($pattern->getId())->save();
                }
            }
        }

        //parse order ID from email subject
        preg_match_all('[[0-9]{9}]', $email->getSubject(), $numbers);
        foreach ($numbers[0] as $number) {
            $orders = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('increment_id', $number)
                ->addFieldToFilter('customer_id', $customer->getId());

            if (count($orders)) {
                // Case 1: this is registered customer and has an order
                $order = Mage::getModel('sales/order')->loadByAttribute('increment_id', $number);
                $ticket->setCustomerId($order->getCustomerId());
                $ticket->setOrderId($order->getId());
                $ticket->save();
                break;
            } else {
                $order = Mage::getModel('sales/order')->loadByAttribute('increment_id', $number);
                $ticket->setOrderId($order->getId());

                // Case 2: this is known guest customer or known another email of registered customer
                $prevTickets = Mage::getModel('helpdesk/ticket')->getCollection()
                    ->addFieldToFilter('customer_email', $email->getFromEmail())
                    ->addFieldToFilter('order_id', $order->getId());
                if (count($prevTickets)) {
                    $ticket->setCustomerId($order->getCustomerId());
                    $ticket->save();
                    break;
                }

                // Case 3: this is generic guest customer with existing order
                $quotes = Mage::getModel('sales/order_address')->getCollection();
                $quotes
                    ->addFieldToFilter('email', $email->getFromEmail());
                $quotes->getSelect()->group('email');
                if (count($quotes)) {
                    $ticket->setQuoteAddressId($quotes->getFirstItem()->getId());
                    $ticket->save();
                    break;
                }
            }
        }

        //add message to ticket
        $text = $email->getBody();
        $encodingHelper = Mage::helper('helpdesk/encoding');
        $text = $encodingHelper->toUTF8($text);
        $body = Mage::helper('helpdesk/string')->parseBody($text, $email->getFormat());
        $ticket->addMessage($body, $customer, $user, $triggeredBy, $messageType, $email);

        Mage::dispatchEvent('helpdesk_process_email', array('body' => $body, 'customer' => $customer, 'user' => $user, 'ticket' => $ticket));

        Mage::helper('helpdesk/history')->changeTicket($ticket, $triggeredBy, array('user' => $user, 'customer' => $customer));

        return $ticket;
    }

    public function checkForSpamPattern($email)
    {
        $patterns = Mage::getModel('helpdesk/pattern')->getCollection()
            ->addFieldToFilter('is_active', true);
        foreach ($patterns as $pattern) {
            if ($pattern->checkEmail($email)) {
                return $pattern;
            }
        }

        return false;
    }

    /**
     * Merge selected tickets.
     *
     * @param array $ids - array of ticket identifiers
     */
    public function mergeTickets($ids)
    {
        // Sort ids in ascending order
        sort($ids);

        $baseTicket = Mage::getModel('helpdesk/ticket')->load($ids[0]);

        // Get all messages, registered in selected tickets and merge it to oldest
        $mergeMessages = Mage::getModel('helpdesk/message')->getCollection()
                                ->addFieldToFilter('ticket_id', $ids);
        foreach ($mergeMessages as $msg) {
            $msg->setTicketId($baseTicket->getId());
            $msg->save();
        }

        // Add to merged tickets new message instead of moved ones
        $mergeMessage = 'Ticket was merged to '.$baseTicket->getCode().' - '.$baseTicket->getName();
        /** @var Mage_Admin_Model_User $user */
        /* @noinspection PhpUndefinedMethodInspection */
        $user = Mage::getSingleton('admin/session')->getUser();
        $mergeCodes = array();
        foreach ($ids as $id) {
            if ($id == $baseTicket->getId()) {
                continue;
            }

            $ticket = Mage::getModel('helpdesk/ticket')->load($id);
            $ticket
                ->setMergedTicketId($baseTicket->getId())
                ->setIsArchived(true)
                ->addMessage($mergeMessage, null, $user, Mirasvit_Helpdesk_Model_Config::USER,
                    Mirasvit_Helpdesk_Model_Config::MESSAGE_INTERNAL)
                ->save();
            $mergeCodes[] = $ticket->getCode();
            Mage::helper('helpdesk/history')->changeTicket($ticket, Mirasvit_Helpdesk_Model_Config::USER, array('user' => $user));
        }
        Mage::helper('helpdesk/history')->changeTicket($baseTicket, Mirasvit_Helpdesk_Model_Config::USER, array('user' => $user, 'codes' => $mergeCodes));
    }
}
