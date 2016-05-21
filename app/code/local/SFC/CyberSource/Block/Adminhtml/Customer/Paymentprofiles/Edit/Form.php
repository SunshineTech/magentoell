<?php
/**
 * StoreFront CyberSource Tokenized Payment Extension for Magento
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of StoreFront Consulting, Inc.
 *
 * @category  SFC
 * @package   SFC_CyberSource
 * @author    Garth Brantley <garth@storefrontconsulting.com>
 * @copyright 2009-2013 StoreFront Consulting, Inc. All Rights Reserved.
 * @license   http://www.storefrontconsulting.com/media/downloads/ExtensionLicense.pdf StoreFront Consulting Commercial License
 * @link      http://www.storefrontconsulting.com/cybersource-saved-credit-cards-extension-for-magento/
 *
 */

class  SFC_CyberSource_Block_Adminhtml_Customer_Paymentprofiles_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare edit form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' => 'multipart/form-data',
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
