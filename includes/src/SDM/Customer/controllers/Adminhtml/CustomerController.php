<?php
/**
 * Separation Degrees One
 *
 * Ellison's Mage_Customer customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Customer
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

require_once
    Mage::getModuleDir('controllers', 'SFC_CyberSource') . DS . 'Override/Admin/CustomerController.php'
;

/**
 * SDM_Customer_Adminhtml_CustomerController class
 */
class SDM_Customer_Adminhtml_CustomerController
    extends SFC_CyberSource_Override_Admin_CustomerController
{
    /**
     * Save the application status
     * @return void
     */
    public function saveAction()
    {
        parent::saveAction();
        $customer = Mage::registry('current_customer');
        $application = Mage::getModel('retailerapplication/application')
            ->loadByCustomer($customer);
        $account = $this->getRequest()->getPost('account');

        // Get extra application fields
        if ($application->getId()) {
            if (isset($account['application_status'])) {
                $application->setStatus($account['application_status'])->save();
            }
            if (isset($account['admin_notes'])) {
                $application->setAdminNotes($account['admin_notes'])->save();
            }
        }

        // Save internal comments
        if (isset($account['internal_notes'])) {
            // Object needs to be re-loaded due to removed addresses being recreated
            // otherwise
            Mage::getModel('customer/customer')->load($customer->getId())
                ->setInternalNotes($account['internal_notes'])
                ->save();
        }
    }
}
