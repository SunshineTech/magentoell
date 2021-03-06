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



class Mirasvit_Helpdesk_Helper_Notification extends Mage_Core_Helper_Abstract
{
    //http://www.iana.org/assignments/auto-submitted-keywords/auto-submitted-keywords.xhtml
    const FLAG_AUTO_REPLIED = 'auto-replied';

    const XML_PATH_DESIGN_EMAIL_LOGO = 'design/email/logo';
    const XML_PATH_DESIGN_EMAIL_LOGO_ALT = 'design/email/logo_alt';

    public $emails = array();

    protected function getConfig()
    {
        return Mage::getSingleton('helpdesk/config');
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Ticket $ticket
     * @param Mage_Customer_Model_Customer   $customer
     * @param Mage_Admin_Model_User          $user
     */
    protected function notifyUser($ticket, $customer, $user)
    {
        $storeId = $ticket->getStoreId();
        if ($ticket->getUserId()) {
            $user = Mage::getModel('admin/user');
            $user->load($ticket->getUserId());
            $this->mail($ticket, $customer, $user, $user->getEmail(), $user->getName(), $this->getConfig()->getNotificationStaffNewMessageTemplate($storeId), $ticket->getLastMessage()->getAttachments());
        } elseif ($department = $ticket->getDepartment()) {
            if ($department->getNotificationEmail()) {
                $this->mail($ticket, $customer, $user, $department->getNotificationEmail(), $department->getName(), $this->getConfig()->getNotificationStaffNewMessageTemplate($storeId), $ticket->getLastMessage()->getAttachments());
            }
            if ($department->getIsMembersNotificationEnabled()) {
                foreach ($department->getUsers() as $member) {
                    $this->mail($ticket, $customer, $user, $member->getEmail(), $department->getName(), $this->getConfig()->getNotificationStaffNewMessageTemplate($storeId), $ticket->getLastMessage()->getAttachments());
                }
            }
        }
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Ticket $ticket
     * @param Mage_Customer_Model_Customer   $customer
     * @param Mage_Admin_Model_User          $user
     */
    protected function notifyCustomer($ticket, $customer, $user)
    {
        $storeId = $ticket->getStoreId();
        $this->mail($ticket, $customer, $user, $ticket->getCustomerEmail(), $ticket->getCustomerName(), $this->getConfig()->getNotificationNewMessageTemplate($storeId), $ticket->getLastMessage()->getAttachments());
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Ticket $ticket
     * @param Mage_Customer_Model_Customer   $customer
     * @param Mage_Admin_Model_User          $user
     */
    protected function notifyThird($ticket, $customer, $user)
    {
        $storeId = $ticket->getStoreId();
        $this->mail($ticket, $customer, $user, $ticket->getThirdPartyEmail(), '', $this->getConfig()->getNotificationThirdNewMessageTemplate($storeId), $ticket->getLastMessage()->getAttachments());
    }

    /**
     * Send email notification about creation of new ticket.
     *
     * @param Mirasvit_Helpdesk_Model_Ticket $ticket
     * @param Mage_Customer_Model_Customer   $customer
     * @param Mage_Admin_Model_User          $user
     * @param $triggeredBy
     * @param $messageType
     */
    public function newTicket($ticket, $customer, $user, $triggeredBy, $messageType)
    {
        $storeId = $ticket->getStoreId();

        if ($triggeredBy == Mirasvit_Helpdesk_Model_Config::CUSTOMER) {
            $this->mail($ticket, $customer, $user, $ticket->getCustomerEmail(), $ticket->getCustomerName(), $this->getConfig()->getNotificationNewTicketTemplate($storeId), array(), array(), self::FLAG_AUTO_REPLIED);

            if ($department = $ticket->getDepartment()) {
                if ($department->getNotificationEmail()) {
                    $this->mail($ticket, $customer, $user, $department->getNotificationEmail(), $department->getName(), $this->getConfig()->getNotificationStaffNewTicketTemplate($storeId), $ticket->getLastMessage()->getAttachments());
                }
                if ($department->getIsMembersNotificationEnabled()) {
                    foreach ($department->getUsers() as $member) {
                        $this->mail($ticket, $customer, $member, $member->getEmail(), $department->getName(), $this->getConfig()->getNotificationStaffNewTicketTemplate($storeId), $ticket->getLastMessage()->getAttachments());
                    }
                }
            }
        } else {
            $this->newMessage($ticket, $customer, $user, $triggeredBy, $messageType);
        }

        Mage::helper('helpdesk/ruleevent')->newEvent(Mirasvit_Helpdesk_Model_Config::RULE_EVENT_NEW_TICKET, $ticket);
    }

    /**
     * Send email notification about new message in the ticket.
     *
     * @param Mirasvit_Helpdesk_Model_Ticket $ticket
     * @param Mage_Customer_Model_Customer   $customer
     * @param Mage_Admin_Model_User          $user
     * @param $triggeredBy
     * @param $messageType
     */
    public function newMessage($ticket, $customer, $user, $triggeredBy, $messageType)
    {
        if ($messageType == Mirasvit_Helpdesk_Model_Config::MESSAGE_PUBLIC) {
            if ($triggeredBy == Mirasvit_Helpdesk_Model_Config::CUSTOMER) {
                $this->notifyUser($ticket, $customer, $user, $triggeredBy);
                Mage::helper('helpdesk/ruleevent')->newEvent(Mirasvit_Helpdesk_Model_Config::RULE_EVENT_NEW_CUSTOMER_REPLY, $ticket);
            } elseif ($triggeredBy == Mirasvit_Helpdesk_Model_Config::USER) {
                $this->notifyCustomer($ticket, $customer, $user, $triggeredBy);
                Mage::helper('helpdesk/ruleevent')->newEvent(Mirasvit_Helpdesk_Model_Config::RULE_EVENT_NEW_STAFF_REPLY, $ticket);
            }
        } elseif ($messageType == Mirasvit_Helpdesk_Model_Config::MESSAGE_PUBLIC_THIRD ||
            $messageType == Mirasvit_Helpdesk_Model_Config::MESSAGE_INTERNAL_THIRD) {
            if ($triggeredBy == Mirasvit_Helpdesk_Model_Config::THIRD) {
                $this->notifyUser($ticket, $customer, $user, $triggeredBy);
                Mage::helper('helpdesk/ruleevent')->newEvent(Mirasvit_Helpdesk_Model_Config::RULE_EVENT_NEW_THIRD_REPLY, $ticket);
            } elseif ($triggeredBy == Mirasvit_Helpdesk_Model_Config::USER) {
                $this->notifyThird($ticket, $customer, $user, $triggeredBy);
            }
        } elseif ($messageType == Mirasvit_Helpdesk_Model_Config::MESSAGE_INTERNAL) {
            /** @var Mage_Admin_Model_User $currentUser */
            /* @noinspection PhpUndefinedMethodInspection */
            $currentUser = Mage::getSingleton('admin/session')->getUser();
            if ($ticket->getUserId() == 0 || $ticket->getUserId() !== $currentUser->getId()) {
                $this->notifyUser($ticket, $customer, $user, $triggeredBy);
            }
        }
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Ticket       $ticket
     * @param Mage_Customer_Model_Customer|false   $customer
     * @param Mage_Admin_Model_User|false          $user
     * @param $recipientEmail
     * @param $recipientName
     * @param $templateName
     * @param Mirasvit_Helpdesk_Model_Attachment[] $attachments
     * @param array                                $variables
     * @param bool                                 $emailFlag
     *
     * @return bool
     *
     * @throws Exception
     * @throws Zend_Mail_Exception
     */
    public function mail($ticket, $customer, $user, $recipientEmail, $recipientName, $templateName, $attachments = array(), $variables = array(), $emailFlag = false)
    {
        if ($templateName == 'none') {
            return false;
        }

        $storeId = $ticket->getStoreId();
        $config = Mage::getSingleton('helpdesk/config');
        if ($config->getDeveloperIsActive($storeId)) {
            if ($sandboxEmail = $config->getDeveloperSandboxEmail($storeId)) {
                $recipientEmail = $sandboxEmail;
            }
        }
        $department = $ticket->getDepartment();
        $store = $ticket->getStore();

        if (!$customer) {
            $customer = $ticket->getCustomer();
        }
        if (!$user) {
            $user = $ticket->getUser();
        }

        // save current design settings
        $currentDesignConfig = clone $this->_getDesignConfig();
        $this->_setDesignConfig(array('area' => 'frontend', 'store' => $store->getId()));
        $this->_applyDesignConfig();

        $variables = array_merge($variables,
            array(
                'ticket' => $ticket,
                'customer' => $customer,
                'user' => $user,
                'department' => $department,
                'store' => $store,
                'hidden_separator' => Mage::helper('helpdesk/email')->getHiddenSeparator(),
                'logo_url' => $this->_getLogoUrl($store),
                'logo_alt' => $this->_getLogoAlt($store),
            )
        );

        if (isset($variables['email_subject'])) {
            $variables['email_subject'] = $this->processVariable($variables['email_subject'], $variables);
        }
        if (isset($variables['email_body'])) {
            $variables['email_body'] = $this->processVariable($variables['email_body'], $variables);
        }

        $senderName = Mage::getStoreConfig("trans_email/ident_{$department->getSenderEmail()}/name", $storeId);
        $senderEmail = Mage::getStoreConfig("trans_email/ident_{$department->getSenderEmail()}/email", $storeId);

        if (!$senderEmail) {
            return;
        }
        if (!$recipientEmail) {
            return;
        }
        $template = Mage::getModel('core/email_template');
        foreach ($attachments as $attachment) {
            /* @noinspection PhpUndefinedClassInspection */
            $template->getMail()->createAttachment($attachment->getBody(), $attachment->getType(), Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $attachment->getName());
        }

        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        if ($emailFlag) {
            $template->getMail()->addHeader('Auto-Submitted', $emailFlag);
        }

        // Mandrill NOT ABLE to send CC - so we need an overwork
        if (method_exists($template->getMail(), 'addCc')) {
            if (count($ticket->getCc())) {
                $template->getMail()->addCc($ticket->getCc());
            }
        } else {
            $recipientEmail = array_unique(array_merge((array) $recipientEmail, $ticket->getCc()));
        }

        $bcc = array_unique(array_merge($this->getConfig()->getGeneralBccEmail($storeId), $ticket->getBcc()));
        if (count($bcc)) {
            $template->getMail()->addBcc($bcc);
        }
        $template->setDesignConfig(array('area' => 'frontend', 'store' => $storeId));
        $template
            ->sendTransactional($templateName,
                array(
                    'name' => $senderName,
                    'email' => $senderEmail,
                ),
                $recipientEmail, $recipientName, $variables, $storeId);
        $text = $template->getProcessedTemplate($variables, true);
        $this->emails[] = $text;

        $translate->setTranslateInline(true);

        // restore previous design settings
        $this->_setDesignConfig($currentDesignConfig->getData());
        $this->_applyDesignConfig();
    }

    public function processVariable($variable, $variables)
    {
        $template = Mage::getModel('core/email_template');
        $template->setTemplateText($variable);

        return $template->getProcessedTemplate($variables);
    }

    protected function _getLogoUrl($store)
    {
        $store = Mage::app()->getStore($store);
        $fileName = $store->getConfig(self::XML_PATH_DESIGN_EMAIL_LOGO);
        if ($fileName) {
            $uploadDir = Mage_Adminhtml_Model_System_Config_Backend_Email_Logo::UPLOAD_DIR;
            $fullFileName = Mage::getBaseDir('media').DS.$uploadDir.DS.$fileName;
            if (file_exists($fullFileName)) {
                return Mage::getBaseUrl('media').$uploadDir.'/'.$fileName;
            }
        }

        return Mage::getDesign()->getSkinUrl('images/logo_email.gif');
    }

    protected function _getLogoAlt($store)
    {
        $store = Mage::app()->getStore($store);
        $alt = $store->getConfig(self::XML_PATH_DESIGN_EMAIL_LOGO_ALT);
        if ($alt) {
            return $alt;
        }

        return $store->getFrontendName();
    }

    protected $_designConfig;

    protected function _setDesignConfig(array $config)
    {
        $this->_getDesignConfig()->setData($config);

        return $this;
    }

    protected function _getDesignConfig()
    {
        if (is_null($this->_designConfig)) {
            $store = is_object(Mage::getDesign()->getStore())
                ? Mage::getDesign()->getStore()->getId()
                : Mage::getDesign()->getStore();

            $this->_designConfig = new Varien_Object(array(
                'area' => Mage::getDesign()->getArea(),
                'store' => $store,
            ));
        }

        return $this->_designConfig;
    }

    protected function _applyDesignConfig()
    {
        $designConfig = $this->_getDesignConfig();
        $design = Mage::getDesign();
        $designConfig->setOldArea($design->getArea())
            ->setOldStore($design->getStore());

        if ($designConfig->hasData('area')) {
            Mage::getDesign()->setArea($designConfig->getArea());
        }
        if ($designConfig->hasData('store')) {
            $store = $designConfig->getStore();
            Mage::app()->setCurrentStore($store);

            $locale = new Zend_Locale(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE, $store));
            Mage::app()->getLocale()->setLocale($locale);
            Mage::app()->getLocale()->setLocaleCode($locale->toString());
            if ($designConfig->hasData('area')) {
                Mage::getSingleton('core/translate')->setLocale($locale)
                    ->init($designConfig->getArea(), true);
            }
            $design->setStore($store);
            $design->setTheme('');
            $design->setPackageName('');
        }

        return $this;
    }
}
