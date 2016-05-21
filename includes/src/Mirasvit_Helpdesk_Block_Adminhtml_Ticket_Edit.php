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



class Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'ticket_id';
        $this->_controller = 'adminhtml_ticket';
        $this->_blockGroup = 'helpdesk';
        $this->_removeButton('back');
        $this->_removeButton('reset');
        $this->_removeButton('save');

        if ($this->isArchive()) {
            $this->_updateButton('delete', 'label', Mage::helper('helpdesk')->__('Delete'));
            $this->_addButton('restore', array(
                    'label' => Mage::helper('helpdesk')->__('Restore Ticket'),
                    'onclick' => 'setLocation(\''.$this->getRestoreUrl().'\')',
                    'class' => 'save',
                ));
        } elseif ($this->getTicket()) {
            $this->_removeButton('delete');
            $this->_addButton('archive', array(
                    'label' => Mage::helper('helpdesk')->__('Archive'),
                    'onclick' => "deleteConfirm('Are you sure you want to do this?','".$this->getArchiveUrl()."')",
                    'class' => 'delete',
                ));
            $this->_addButton('spam', array(
                'label' => Mage::helper('helpdesk')->__('Spam'),
                'onclick' => 'setLocation(\''.$this->getSpamUrl().'\')',
                'class' => 'delete',
                'style' => 'margin-right: 40px',
            ));
        } else {
            $this->_addButton('update', array(
                'label' => Mage::helper('helpdesk')->__('Create Ticket'),
                'onclick' => 'saveEdit(this)',
                'class' => 'save saveTicketBtn',
            ), -100);
        }
        if ($this->getTicket()) {
            if (Mage::getSingleton('helpdesk/config')->isActiveRma()) {
                $this->_addButton('rma', array(
                        'label' => Mage::helper('helpdesk')->__('Convert To RMA'),
                        'onclick' => 'var win=window.open(\''.$this->getRmaUrl().'\', \'_blank\'); win.focus();',
                        // 'onclick'   => 'setLocation(\'' . $this->getRmaUrl() . '\')',
                        'class' => 'add',
                    ));
            }
            $this->_addButton('update_continue', array(
                'label' => Mage::helper('helpdesk')->__('Update And Continue Edit'),
                'id' => 'saveAndContinueTicketBtn',
                'onclick' => 'saveAndContinueEdit(this)',
                'class' => 'save saveAndContinueTicketBtn',
            ), -100);
            $this->_addButton('update', array(
                'label' => Mage::helper('helpdesk')->__('Update'),
                'id' => 'saveTicketBtn',
                'onclick' => 'saveEdit(this)',
                'class' => 'save saveTicketBtn',
            ), -100);
        }

        $this->_addButton('back', array(
            'label' => Mage::helper('adminhtml')->__('Back'),
            'onclick' => 'setLocation(\''.Mage::helper('adminhtml')->getUrl('*/*/').'\')',
            'class' => 'back',
            'level' => -1,
        ));

        $this->_formScripts[] = "
            function saveEdit(clicked){
                clicked.disabled = true;
                isAllowDraft=false;
                editForm.submit($('edit_form').action);
            }
            function saveAndContinueEdit(clicked){
                clicked.disabled = true;
                isAllowDraft=false;
                editForm.submit($('edit_form').action + 'back/edit/');
            }
            function checkWYSIWYG(checked) {
                if(tinyMCE.activeEditor != null) {
                    tinyMCE.activeEditor.setContent(tinyMCE.activeEditor.getContent() +
                        document.getElementById('htmltemplate-' + checked.value).innerHTML);

                }
            }
            function switchVisible(controlId, status) {
                // Ensure that cancellation of copy destroys the addresses
                if(status != 'true') {
                    if(controlId == 'allowCC') {
                        $('cc').value = '';
                    } else {
                        $('bcc').value = '';
                    }
                }

                // Switch visibility constant
                $(controlId).value = status;

                // emulate event firing as FormDependency Controller observes events not values
                if(document.createEventObject) {
                    var evt = document.createEventObject();
                    $(controlId).fireEvent('onchange', evt);
                } else {
                    var evt = document.createEvent('HTMLEvents');
                    evt.initEvent('change', false, true);
                    $(controlId).dispatchEvent(evt);
                }
            }
        ";
        if (!Mage::helper('helpdesk/permission')->isTicketRemoveAllowed()) {
            $this->_removeButton('delete');
        }

        return $this;
    }

    public function isArchive()
    {
        return Mage::registry('is_archive');
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/', array('is_archive' => $this->isArchive()));
    }

    public function getRmaUrl()
    {
        return $this->getUrl('rmaadmin/adminhtml_rma/convertticket', array('id' => $this->getTicket()->getId()));
    }

    public function getSpamUrl()
    {
        return $this->getUrl('*/*/spam', array('id' => $this->getTicket()->getId()));
    }

    public function getArchiveUrl()
    {
        return $this->getUrl('*/*/archive', array('id' => $this->getTicket()->getId()));
    }

    public function getRestoreUrl()
    {
        return $this->getUrl('*/*/restore', array('id' => $this->getTicket()->getId()));
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('helpdesk/config_wysiwyg')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    public function getTicket()
    {
        if (Mage::registry('current_ticket') && Mage::registry('current_ticket')->getId()) {
            return Mage::registry('current_ticket');
        }
    }

    public function getHeaderText()
    {
        if ($ticket = $this->getTicket()) {
            return Mage::helper('helpdesk')->__('%s', $this->htmlEscape('[#'.$ticket->getCode().'] '.$ticket->getName()));
        } else {
            return Mage::helper('helpdesk')->__('Create New Ticket');
        }
    }

    /************************/
}
