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
 * @copyright Copyright (c) 2015 Separation Degrees One (http:__www.separationdegrees.com)
 */

require_once
    Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'Customer' . DS
        . 'GroupController.php'
;

/**
 * SDM_Customer_Adminhtml_Customer_GroupController class
 */
class SDM_Customer_Adminhtml_Customer_GroupController extends Mage_Adminhtml_Customer_GroupController
{
    /**
     * Create or save customer group. Sets custom fields as well.
     *
     * @return void
     */
    public function saveAction()
    {
        $customerGroup = Mage::getModel('customer/group');
        $id = $this->getRequest()->getParam('id');
        if (!is_null($id)) {
            $customerGroup->load((int)$id);
        }

        $taxClass = (int)$this->getRequest()->getParam('tax_class');
        $position = (int)$this->getRequest()->getParam('position');
        $minQtyFlag = (int)$this->getRequest()->getParam('min_qty_override');

        if ($taxClass) {
            try {
                $customerGroupCode = (string)$this->getRequest()->getParam('code');

                if (!empty($customerGroupCode)) {
                    $customerGroup->setCode($customerGroupCode);
                }

                // Setting custom fields
                $customerGroup->setTaxClassId($taxClass)
                    ->setPosition($position)
                    ->setMinQtyOverride($minQtyFlag)
                    ->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('customer')->__('The customer group has been saved.')
                );
                $this->getResponse()->setRedirect($this->getUrl('*/customer_group'));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setCustomerGroupData($customerGroup->getData());
                $this->getResponse()->setRedirect($this->getUrl('*/customer_group/edit', array('id' => $id)));
                return;
            }
        } else {
            $this->_forward('new');
        }
    }
}
