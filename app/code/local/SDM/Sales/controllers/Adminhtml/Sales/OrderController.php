<?php
/**
 * Separation Degrees One
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Sales
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

require_once
    Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'Sales' . DS
    . 'OrderController.php'
;

/**
 * SDM_Sales_Adminhtml_Sales_OrderController class
 */
class SDM_Sales_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    /**
     * Save the AX account number for an order
     *
     * @see Mage_Adminhtml_Sales_OrderController::addCommentAction
     *
     * @return void
     */
    public function saveAxAccountIdAction()
    {
        $response = false;
        $data = $this->getRequest()->getPost('order');

        if ($this->_initOrder()) {
            if (isset($data['ax_account_id'])) {
                try {
                    $axId = trim($data['ax_account_id']);

                    // Save AX invoice account ID
                    Mage::getModel('customer/customer')
                        ->load(Mage::registry('current_order')->getCustomerId())
                        ->setAxCustomerId($axId)
                        ->save();

                    // Render block
                    $this->loadLayout('empty');
                    $this->renderLayout();

                } catch (Exception $e) {
                    $response = array(
                        'error'     => true,
                        'message'   => $this->__('Cannot add AX account ID. Error: ' . $e->getMessage())
                    );
                }
            }

            // Response is set instead of rendering the block if there is an error
            if (is_array($response)) {
                $response = Mage::helper('core')->jsonEncode($response);
                $this->getResponse()->setBody($response);
            }
        }
    }

    /**
     * Save the AX invoice number for an order
     *
     * @see Mage_Adminhtml_Sales_OrderController::addCommentAction
     *
     * @return void
     */
    public function saveAxInvoiceIdAction()
    {
        $response = false;
        $data = $this->getRequest()->getPost('order');

        if ($this->_initOrder()) {
            if (isset($data['ax_invoice_id'])) {
                try {
                    $axId = trim($data['ax_invoice_id']);

                    // Save AX invoice account ID
                    Mage::getModel('customer/customer')
                        ->load(Mage::registry('current_order')->getCustomerId())
                        ->setAxInvoiceId($axId)
                        ->save();

                    // Render block
                    $this->loadLayout('empty');
                    $this->renderLayout();

                } catch (Exception $e) {
                    $response = array(
                        'error'     => true,
                        'message'   => $this->__('Cannot add AX invoice ID. Error: ' . $e->getMessage())
                    );
                }
            }

            // Response is set instead of rendering the block if there is an error
            if (is_array($response)) {
                $response = Mage::helper('core')->jsonEncode($response);
                $this->getResponse()->setBody($response);
            }
        }
    }
}
