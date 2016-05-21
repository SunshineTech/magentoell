<?php
/**
 * Separation Degrees One
 *
 * Implements the customer/retailer discount logic for viewing and obtaining
 * discounts and prices, as wel as managing the customer groups.
 *
 * PHP version 5.5
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CustomerDiscount
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Admin controller
 */
class SDM_CustomerDiscount_Adminhtml_DiscountgroupController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Checks if admin is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/customer/customerdiscount');
    }

    /**
     * Initialize layout, menu, and breadcrumb for all adminhtml actions
     *
     * @return SDM_CustomerDiscount_Adminhtml_GroupController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('customer/discountgroup_index');
        $this->_title($this->__('Customer'))->_title($this->__('Retailer Discount'));

        return $this;
    }

    /**
     * Grid
     *
     * @return null
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * New action
     *
     * @return null
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit action
     *
     * @return null
     */
    public function editAction()
    {
        $discountGroup = Mage::getModel('customerdiscount/discountgroup');

        // Try loading the corresponding object
        if ($id  = $this->getRequest()->getParam('id')) {
            $discountGroup->load($id);

            if (!$discountGroup->getId()) {
                Mage::getSingleton('adminhtml/session')
                    ->addError($this->__('Product line doesn\'t exist'));
                return $this->_redirectReferer();
            }
            $title = 'Edit Product Line';
        } else {
            $title = 'New Product Line';
        }

        Mage::register('discount_group', $discountGroup);    // For the view

        $block = $this->getLayout()
            ->createBlock('customerdiscount/adminhtml_discountgroup_edit');

        $this->_initAction()
            ->_title($this->__('Edit'))
            ->_addContent($block)
            ->renderLayout();
    }

    /**
     * Save action
     *
     * @return null
     */
    public function saveAction()
    {
        if ($post = $this->getRequest()->getPost()) {
            try {
                if (isset($post['id'])) {
                    $discountGroup = Mage::getModel('customerdiscount/discountgroup')
                        ->load($post['id']);
                } else {    // New record
                    $discountGroup = Mage::getModel('customerdiscount/discountgroup');
                }
                $amount = $post['amount'];
                if ($amount > 100) {
                    $amount = 100;
                    $this->_getSession()->addNotice(
                        $this->__("Discount amount cannot exceed 100. Adjusted to 100.")
                    );
                } elseif ($amount < 0) {
                    $amount = 0;
                    $this->_getSession()->addNotice(
                        $this->__("Discount amount cannot be below 0. Adjusted to 0.")
                    );
                }

                $discountGroup
                    ->setCustomerGroupId($post['customer_group_id'])
                    ->setCategoryId($post['category_id'])
                    ->setAmount($amount)
                    ->save();

                $this->_getSession()->addSuccess(
                    $this->__("Group discount ID {$discountGroup->getId()} has been saved.")
                );

                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect(
                        '*/*/edit',
                        array(
                            'id' => $discountGroup->getId(),
                        )
                    );
                } else {
                    // Redirect to remove $_POST data from the request
                    return $this->_redirect('*/*/index');
                }


            } catch (Exception $e) {
                Mage::helper('customerdiscount')->log($e->getMessage());
                $this->_getSession()->addError($e->getMessage());
                return $this->_redirectReferer();
            }
        }

        return $this->_redirectReferer();
    }

    /**
     * Delete action
     *
     * @return null
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            Mage::getModel('customerdiscount/discountgroup')->load($id)->delete();
            $this->_getSession()->addSuccess('Customer group discount deleted');

            return $this->_redirect('*/*/index');
        }

        $this->_getSession()->addError('Unable to delete customer group discount');
        return $this->_redirectReferer();
    }

    /**
     * Matrix view
     *
     * @return null
     */
    public function matrixAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }
}
