<?php
/**
 * Separation Degrees One
 *
 * Ellison's negotiated product prices
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_NegotiatedProduct
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_NegotiatedProduct_Adminhtml_NegotiatedproductController class
 */
class SDM_NegotiatedProduct_Adminhtml_NegotiatedproductController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        return $this->_redirectReferer();
    }

    /**
     * ACL check
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Adds negotiated product prices
     *
     * @return void
     */
    public function addAction()
    {
        $post = $this->getRequest()->getPost();
        $customer = $this->_initCustomer($post['customer_id']);
        $website = Mage::app()->getWebsite()->load($customer->getWebsiteId());

        // Only valid for retaier website
        if ($website->getCode() != SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_RE) {
            Mage::getSingleton('adminhtml/session')->addError(
                'Negotiated products can be added only to customer accounts on ERUS'
            );
            return $this->_redirectToTab($post['customer_id']);
        }

        if (isset($post['product_data'])) {
            $messages = Mage::helper('negotiatedproduct')
                ->addProducts($post['product_data'], $customer);

            if (!empty($messages['success'])) {
                foreach ($messages['success'] as $message) {
                    Mage::getSingleton('adminhtml/session')
                        ->addSuccess($message);
                }
            }

            if (!empty($messages['fail'])) {
                foreach ($messages['fail'] as $message) {
                    Mage::getSingleton('adminhtml/session')
                        ->addError($message);
                }
            }
        }

        return $this->_redirectToTab($post['customer_id']);
    }

    /**
     * Deletes multiple negotiated price records
     *
     * @return void
     */
    public function massDeleteAction()
    {
        $post = $this->getRequest()->getPost();

        foreach ($post as $key => $param) {
            if (strpos($key, 'checkbox_') !== false) {
                $id = (int)str_replace('checkbox_', '', $key);

                try {
                    $product = Mage::getModel('negotiatedproduct/negotiatedproduct')->load($id);
                    $sku = $product->getSku();
                    $product->delete();
                    Mage::getSingleton('adminhtml/session')
                        ->addSuccess("Deleted record ID $id/SKU $sku");

                } catch (Exception $e) {
                    $this->log(
                        "Failed to delete negotaited record ID $id/SKU $sku. "
                            . 'Error: ' . $e->getMessage()
                    );
                }
            }
        }

        return $this->_redirectToTab($post['customer_id']);
    }

    /**
     * Redirects the admin user to the negotiated products tab. Note that
     * the Mage registry is not available in this action.
     *
     * @param int $customerId
     *
     * @return void
     */
    protected function _redirectToTab($customerId)
    {
        return $this->_redirect(    // Return back to the tab
            'adminhtml/customer/edit',
            array(
                'active_tab' => 'customer_edit_tab_negotiatedproduct',
                'id' => $customerId
            )
        );
    }

    /**
     * Initialize the current customer
     *
     * @param int $id
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _initCustomer($id)
    {
        $customer = Mage::getModel('customer/customer')->load($id);

        Mage::register('current_customer', $customer);

        return $customer;
    }

    /**
     * Get customer's id
     *
     * @return integer
     */
    protected function _getCustomerId()
    {
        return Mage::registry('current_customer')->getId();
    }
}
