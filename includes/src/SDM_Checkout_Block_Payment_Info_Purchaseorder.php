<?php
/**
 * Separation Degrees One
 *
 * Checkout-related customization
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Checkout
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Purchase order block class
 */
class SDM_Checkout_Block_Payment_Info_Purchaseorder extends Mage_Payment_Block_Info_Purchaseorder
{
    protected $_upload = null;

    /**
     * Initialize
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        // Custom template is required only in certain areas
        if (Mage::app()->getStore()->isAdmin()
            || Mage::app()->getRequest()->getControllerName() === 'onepage'
        ) {
            $this->setTemplate('payment/info/purchaseorder.phtml');
        } else {
            $this->setTemplate('sdm/payment/info/purchaseorder.phtml');
        }
    }

    /**
     * Return the upload record object
     *
     * @param int $parentId
     * @param str $type
     *
     * @return SDM_FileUpload_Model_File
     */
    public function getUpload($parentId, $type)
    {
        $this->_upload = Mage::getModel('sdm_upload/file')->loadByKey($parentId, $type);

        return $this->_upload;
    }

    /**
     * Return the POST URL to upload purchase order
     *
     * @return str
     */
    public function getUploadFileUrl()
    {
        return Mage::getUrl('sdm_upload/file/save', array('_secure'=>true));
    }

    /**
     * Retrieve the order object if available
     *
     * @param integer $id
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder($id = null)
    {
        if ($id) {
            return Mage::getModel('sales/order')->load($id);
        }
        if ($this->hasOrder()) {
            return $this->getData('order');
        }
        if (Mage::registry('current_order')) {
            return Mage::registry('current_order');
        }
        if (Mage::registry('order')) {
            return  Mage::registry('order');
        }
    }
}
