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



class Mirasvit_Helpdesk_Block_Adminhtml_User_Edit_Tab_Helpdesk extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getTabLabel()
    {
        return Mage::helper('adminhtml')->__('Help Desk');
    }

    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    public function _beforeToHtml()
    {
        $this->_initForm();

        return parent::_beforeToHtml();
    }

    protected function _initForm()
    {
        $form = new Varien_Data_Form();
        $model = Mage::registry('permissions_user');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('adminhtml')->__('Help Desk Settings')));

        if (Mage::getSingleton('helpdesk/config')->getGeneralIsWysiwyg()) {
            $fieldset->addField('signature', 'editor', array(
                'label' => Mage::helper('adminhtml')->__('Signature for Emails'),
                'name' => 'signature',
                'value' => $model->getSignature(),
                'config' => Mage::getSingleton('mstcore/wysiwyg_config')->getConfig(),
                'wysiwyg' => true,
                'style' => 'height:20em',
            ));
        } else {
            $fieldset->addField('signature', 'textarea', array(
                'name' => 'signature',
                'label' => Mage::helper('adminhtml')->__('Signature for Emails'),
                'id' => 'signature',
                'value' => $model->getSignature(),
            ));
        }

        $this->setForm($form);
    }
}
