<?php
/**
 * Separation Degrees One
 *
 * Ellison's AX ERP integration
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Ax
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Ax_Adminhtml_AxController class
 */
class SDM_Ax_Adminhtml_AxController extends Mage_Adminhtml_Controller_Action
{
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
     * Runs the order export process
     *
     * @return null
     */
    public function exportOrderAction()
    {
        $response = array();
        $usStores = array(
            SDM_Core_Helper_Data::STORE_CODE_US,
            SDM_Core_Helper_Data::STORE_CODE_ER,
            SDM_Core_Helper_Data::STORE_CODE_EE
        );
        $ukStores = array(
            SDM_Core_Helper_Data::STORE_CODE_UK_BP,
            SDM_Core_Helper_Data::STORE_CODE_UK_EU
        );

        $result1 = Mage::helper('sdm_ax/order')->exportXml($usStores);
        $result2 = Mage::helper('sdm_ax/order')->exportXml($ukStores);

        if ($result1 && $result1) {
            $response['result'] = true;
            $response['message'] = 'US and UK Orders exported.';
        } elseif ($result1) {
            $response['result'] = true; // Note: if set to false, popup message does not appear
            $response['message'] = 'UK orders FAILED to export.';
        } elseif ($result2) {
            $response['result'] = true;
            $response['message'] = 'US orders FAILED to export.';
        } else {
            $response['result'] = true;
            $response['message'] = 'Orders FAILED to export.';
        }

        Mage::app()->getResponse()->setBody(json_encode($response));
    }

    /**
     * Runs the order update process
     *
     * @return null
     */
    public function updateOrderAction()
    {
        $response = array();
        $result = Mage::helper('sdm_ax/order')->processStatusUpdate();

        if ($result) {
            $response['result'] = true;
            $response['message'] = 'Orders updated.';
        } else {
            $response['result'] = true;
            $response['message'] = 'Orders FAILED to update.';
        }

        Mage::app()->getResponse()->setBody(json_encode($response));
    }

    /**
     * Runs the product update process
     *
     * @return null
     */
    public function updateProductAction()
    {
        $response = array();
        $result =  Mage::helper('sdm_ax/product')->updateProducts();

        if ($result) {
            $response['result'] = true;
            $response['message'] = 'Products updated.';
        } else {
            $response['result'] = true;
            $response['message'] = 'Products FAILED to update.';
        }

        Mage::app()->getResponse()->setBody(json_encode($response));
    }
}
