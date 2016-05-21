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



class Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mst_helpdesk/ticket/edit/tab/general.phtml');
    }

    protected function getConfig()
    {
        return Mage::getSingleton('helpdesk/config');
    }

    protected function getGeneralForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $ticket = Mage::registry('current_ticket');

        $fieldset = $form->addFieldset('edit_fieldset', array('legend' => Mage::helper('helpdesk')->__('Ticket Summary')));
        if ($ticket->getId()) {
            $fieldset->addField('ticket_id', 'hidden', array(
                'name' => 'ticket_id',
                'value' => $ticket->getId(),
            ));
        }
        if (!$ticket->getId()) {
            $element = $fieldset->addField('name', 'text', array(
                'label' => Mage::helper('helpdesk')->__('Subject'),
                'name' => 'name',
                'value' => $ticket->getName(),
                'required' => true,
            ));
        }
        $fieldset->addField('status_id', 'select', array(
            'label' => Mage::helper('helpdesk')->__('Status'),
            'name' => 'status_id',
            'value' => $ticket->getStatusId(),
            'values' => Mage::getModel('helpdesk/status')->getCollection()->toOptionArray(),
        ));
        $fieldset->addField('priority_id', 'select', array(
            'label' => Mage::helper('helpdesk')->__('Priority'),
            'name' => 'priority_id',
            'value' => $ticket->getPriorityId(),
            'values' => Mage::getModel('helpdesk/priority')->getCollection()->toOptionArray(),
        ));
        $fieldset->addField('owner', 'select', array(
            'label' => Mage::helper('helpdesk')->__('Ticket Owner'),
            'name' => 'owner',
            'value' => (int) $ticket->getDepartmentId().'_'.(int) $ticket->getUserId(),
            'values' => Mage::helper('helpdesk')->getAdminOwnerOptionArray(),
        ));

        if (Mage::helper('mstcore')->isModuleInstalled('Mirasvit_Rma')) {
            $collection = Mage::getModel('rma/rma')->getCollection()
                            ->addFieldToFilter('ticket_id', $ticket->getId());
            if ($collection->count()) {
                $links = array();
                foreach ($collection as $rma) {
                    $links[] = '<a href="'.$rma->getBackendUrl().'">#'.$rma->getIncrementId().'</a>';
                }
                $fieldset->addField('rma', 'note', array(
                    'label' => Mage::helper('helpdesk')->__('RMA'),
                    'name' => 'rma',
                    'text' => implode(', ', $links),
                ));
            }
        }

        $collection = Mage::helper('helpdesk/field')->getStaffCollection();
        if ($ticket->getStoreId()) {
            $collection->addStoreFilter($ticket->getStoreId());
        }
        foreach ($collection as $field) {
            if ($field->getType() == 'checkbox') {
                $fieldset->addField($field->getCode().'1', 'hidden', array('name' => $field->getCode(), 'value' => 0));
            }
            $fieldset->addField($field->getCode(), $field->getType(), Mage::helper('helpdesk/field')->getInputParams($field, true, $ticket));
        }

        return $form;
    }

    protected function getCustomerForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $ticket = Mage::registry('current_ticket');
        $fieldset = $form->addFieldset('edit_fieldset', array('legend' => Mage::helper('helpdesk')->__('Customer Summary')));
        $element = $fieldset->addField('customer_email', 'text', array(
            'label' => Mage::helper('helpdesk')->__('Customer Email'),
            'name' => 'customer_email',
            'value' => $ticket->getCustomerEmail(),
            'required' => true,
        ));

        // Add two links after email field and set them to javascripts
        $linksCode = '';

        // Add hidden controls to hold dependency triggers
        if ($ticket->getCc()) {
            $fieldset->addField('allowCC', 'hidden', array('name' => 'allowCC', 'value' => 'true'));
        } else {
            $linksCode .= "<a id='email_add_cc' href='#' onClick='switchVisible(\"allowCC\", \"true\"); this.hide();return false;'>Cc</a>";
            $fieldset->addField('allowCC', 'hidden', array('name' => 'allowCC', 'value' => 'false'));
        }

        if ($ticket->getBcc()) {
            $fieldset->addField('allowBCC', 'hidden', array('name' => 'allowBCC', 'value' => 'true'));
        } else {
            $linksCode .=  " <a id='email_add_bcc' href='#' onClick='switchVisible(\"allowBCC\", \"true\"); this.hide(); return false;'>Bcc</a>";
            $fieldset->addField('allowBCC', 'hidden', array('name' => 'allowBCC', 'value' => 'false'));
        }

        $element->setAfterElementHtml($linksCode);

        // Add two fields for CC and BCC
        $fieldset->addField('cc', 'text', array(
            'label' => Mage::helper('helpdesk')->__('Cc'),
            'name' => 'cc',
            'value' => implode(', ', $ticket->getCc()),
            'required' => false,
            'note' => Mage::helper('helpdesk')->__('You can enter few emails separated by comma'),
        ));

        $fieldset->addField('bcc', 'text', array(
            'label' => Mage::helper('helpdesk')->__('Bcc'),
            'name' => 'bcc',
            'value' => implode(', ', $ticket->getBcc()),
            'required' => false,
            'note' => Mage::helper('helpdesk')->__('You can enter few emails separated by comma'),
        ));

        $element = $fieldset->addField('customer_query', 'text', array(
            'label' => Mage::helper('helpdesk')->__('Assigned To Customer'),
            'name' => 'customer_query',
        ));

        $element->setAfterElementHtml("
<button type='button' id='find-customer-btn' data-url='".Mage::helper('adminhtml')->getUrl('helpdeskadmin/adminhtml_ticket/customerfind')."'><span>".Mage::helper('helpdesk')->__('Find Customer').'</span></button>
            ');

        $customersOptions = array();
        $ordersOptions = array(array('label' => $this->__('Unassigned'), 'value' => 0));
        if ($ticket->getCustomerId() || $ticket->getQuoteAddressId()) {
            $customers = Mage::helper('helpdesk')->getCustomerArray(false, $ticket->getCustomerId(), $ticket->getQuoteAddressId());
            $email = false;
            foreach ($customers as $value) {
                $customersOptions[] = array('label' => $value['name'], 'value' => $value['id']);
                $email = $value['email'];
            }

            $orders = Mage::helper('helpdesk')->getOrderArray($email);
            foreach ($orders as $value) {
                $ordersOptions[] = array('label' => $value['name'], 'value' => $value['id']);
            }
        }
        $element = $fieldset->addField('customer_id', 'select', array(
            'name' => 'customer_id',
            'value' => $ticket->getCustomerId() ? $ticket->getCustomerId() : 'address_'.$ticket->getQuoteAddressId(),
            'values' => $customersOptions,
            'style' => $ticket->hasCustomer() ? '' : 'display: none',
        ));
        if ($ticket->getCustomerId()) {
            $element->setAfterElementHtml("<a id='view_customer_link' href='".Mage::helper('helpdesk/mage')->getBackendCustomerUrl($ticket->getCustomerId())."' target='_blank'>".Mage::helper('helpdesk')->__('view').'</a>');
        }
        $element = $fieldset->addField('order_id', 'select', array(
            'label' => Mage::helper('helpdesk')->__('Assigned to Order'),
            'name' => 'order_id',
            'value' => $ticket->getOrderId(),
            'values' => $ordersOptions,
        ));
        if ($ticket->hasCustomer() && $ticket->getOrderId()) {
            $element->setAfterElementHtml("<a id='view_order_link' href='".Mage::helper('helpdesk/mage')->getBackendOrderUrl($ticket->getOrderId())."' target='_blank'>".Mage::helper('helpdesk')->__('view').'</a>');
        }

        return $form;
    }

    protected function getMessageForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $ticket = Mage::registry('current_ticket');

        $fieldset = $form->addFieldset('edit_fieldset', array('legend' => Mage::helper('helpdesk')->__('Reply')));

        $element = $fieldset->addField('reply_type', 'select', array(
            'label' => Mage::helper('helpdesk')->__('Message Type'),
            'name' => 'reply_type',
            'value' => Mirasvit_Helpdesk_Model_Config::MESSAGE_PUBLIC,
            'values' => array(
                Mirasvit_Helpdesk_Model_Config::MESSAGE_PUBLIC => Mage::helper('helpdesk')->__('Message to Customer'),
                Mirasvit_Helpdesk_Model_Config::MESSAGE_INTERNAL => Mage::helper('helpdesk')->__('Internal Note'),
                Mirasvit_Helpdesk_Model_Config::MESSAGE_PUBLIC_THIRD => Mage::helper('helpdesk')->__('Message to Third Party'),
                Mirasvit_Helpdesk_Model_Config::MESSAGE_INTERNAL_THIRD => Mage::helper('helpdesk')->__('Private Message to Third Party'),
            ),
        ));

        $element->setAfterElementHtml("
<div id='reply_note'></div>
        ");

        $element = $fieldset->addField('third_party_email', 'text', array(
            'label' => Mage::helper('helpdesk')->__('Third Party Email'),
            'name' => 'third_party_email',
            'value' => $ticket->getThirdPartyEmail(),
            'required' => true,
            // 'note'      => 'comma separated list'
        ));

        $collection = Mage::getModel('helpdesk/template')->getCollection()
                ->addFieldToFilter('is_active', 1)
                ->setOrder('name', 'asc');
        if ($ticket->getId()) {
            $collection->addStoreFilter($ticket->getStoreId());
        }
        if ($collection->count()) {
            if ($ticket->getId()) {
                $options = $collection->toOptionArray(true);
            } else {
                $options = $collection->toOptionGroupArray(true);
            }
            $element = $fieldset->addField('template_id', 'select', array(
                'label' => Mage::helper('helpdesk')->__('Insert Quick Response'),
                'name' => 'template_id',
                'value' => $ticket->getTemplateId(),
                'onchange' => 'checkWYSIWYG(this)',
                'values' => $options,
            ));

            $values = array();
            foreach ($collection as $template) {
                $text = trim($template->getParsedTemplate($ticket));
                $values[] = "<div id='htmltemplate-{$template->getId()}' style='display:none;'>{$text}</div>";
            }
            $element->setAfterElementHtml(implode("\n", $values));
        }
        $text = '';
        $statusMessage = '';
        if ($draft = Mage::helper('helpdesk/draft')->getSavedDraft($ticket->getId())) {
            $text = $draft->getBody();
            $statusMessage = $draft->getDraftStatusMessage();
        }

        if ($this->getConfig()->getGeneralIsWysiwyg()) {
            $element = $fieldset->addField('reply', 'editor', array(
                'label' => Mage::helper('helpdesk')->__('Message'),
                'required' => false,
                'name' => 'reply',
                'value' => $text,
                'config' => Mage::getSingleton('helpdesk/config_wysiwyg')->getConfig(),
                'wysiwyg' => true,
                'style' => 'height:15em',
            ));
        } else {
            $element = $fieldset->addField('reply', 'textarea', array(
                'label' => Mage::helper('helpdesk')->__('Message'),
                'required' => false,
                'name' => 'reply',
                'value' => $text,
            ));
        }
        if ($statusMessage) {
            $element->setAfterElementHtml("<div id='helpdesk-draft-status'>$statusMessage</div>");
        }
        $fieldset->addField('attachment', 'file', array(
            'name' => 'attachment[]',
            'class' => 'multi',
        ));

        return $form;
    }

    /************************/

    protected function _toHtml()
    {
        // Set dependencies of visible fields from hidden
        $dependency_block = $this->getLayout()
            ->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap('allowCC', 'allowCC')
                ->addFieldMap('allowBCC', 'allowBCC')
                ->addFieldMap('cc', 'cc')
                ->addFieldMap('bcc', 'bcc')
                ->addFieldDependence('cc', 'allowCC', 'true')
                ->addFieldDependence('bcc', 'allowBCC', 'true');

        $messages = $this->getLayout()->createBlock('helpdesk/adminhtml_ticket_edit_tab_messages')->toHtml();

        return parent::_toHtml().$dependency_block->toHtml().$messages;
    }

    public function getTicket()
    {
        return Mage::registry('current_ticket');
    }

    public function getNoticeMessage()
    {
        $ticket = $this->getTicket();
        if (!$ticket->getId()) {
            return;
        }
        $userId = Mage::getSingleton('admin/session')->getUser()->getUserId();

        return Mage::helper('helpdesk/draft')->getNoticeMessage($ticket->getId(), $userId);
    }
}
