<?php
 /**
 * Separation Degrees One
 *
 * Ellison's Mage_Sales customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Customer
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Customer_Block_Adminhtml_Customer_Edit_Tab_Account class
 */
class SDM_Customer_Block_Adminhtml_Customer_Edit_Tab_Account extends Mage_Adminhtml_Block_Customer_Edit_Tab_Account
{
    /**
     * Add fields for retailer application
     *
     * @return $this
     */
    public function initForm()
    {
        $result = parent::initForm();
        $form = $this->getForm();

        $customer = Mage::registry('current_customer');
        $application = Mage::getModel('retailerapplication/application')
            ->loadByCustomer($customer);

        $applicationFieldset = $form->addFieldset(
            'retailer_application',
            array('legend' => Mage::helper('customer')->__('Retailer Application'))
        );

        if ($application->getId()) {
            $applicationFieldset->addField('application_status', 'select',
                array(
                    'label' => Mage::helper('customer')->__('Application Status'),
                    'name'  => 'application_status',
                    'class' => '',
                    'values' => $application->getStatuses(),
                    'value' => $application->getStatus()
                )
            )->setAfterElementHtml(
                '<br><br><p>To view and modify the application, click the "Login as Customer"' .
                ' link and navigate to the retailer application page in the account center.</p>'
            );
            $applicationFieldset->addField('admin_notes', 'textarea',
                array(
                    'label' => Mage::helper('customer')->__('Admin Notes'),
                    'name'  => 'admin_notes',
                    'class' => '',
                    'note' => 'Optional notes regarding this application.' .
                              'These notes will not be shown to the customer.',
                    'value' => $application->getAdminNotes()
                )
            );
        } else {
            $applicationFieldset->addField('ignore_this_field', 'hidden',
                array(
                    'name'  => 'ignore_this_field',
                )
            );
            $form->getElement('ignore_this_field')
                ->setAfterElementHtml('<p>This customer account does not have an associated retailer application.</p>');
        }

        // Add extra information for readability
        $form->getElement('taxvat')->setAfterElementHtml('<br><br><hr><br>');
        $form->getElement('ax_invoice_id')
            ->setAfterElementHtml('<br><br><hr><br><div>Retailer/Education Information</div>');
        $form->getElement('can_use_purchase_order')
            ->setAfterElementHtml(
                '<div>Purchase order payment method must be enabled for ERUS for this to function properly</div>'
            );
        // $form->getElement('tax_exempt')->setAfterElementHtml('<br><br><hr><br>');

        return $this;
    }
}
