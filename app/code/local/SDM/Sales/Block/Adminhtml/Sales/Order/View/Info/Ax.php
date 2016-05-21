<?php
/**
 * Separation Degrees Media
 *
 * Ellison's Mage_Sales customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Sales
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Sales_Block_Adminhtml_Sales_Order_View_Info_Ax class
 */
class SDM_Sales_Block_Adminhtml_Sales_Order_View_Info_Ax
    extends Mage_Adminhtml_Block_Template
{
    /**
     * Cached boolean for EEUS check
     *
     * @var boolean
     */
    protected $_isEeus = null;

    /**
     * Preapre layout
     *
     * @return SDM_Sales_Block_Adminhtml_Sales_Order_View_Info_Ax
     */
    protected function _prepareLayout()
    {
        $onclick = "submitAndReloadArea($('ax_account_id_block'), '".$this->getAxAcountIdSubmitUrl()."')";
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('sales')->__('Save'),
                'class'   => 'save',
                'onclick' => $onclick
            ));
        $this->setChild('ax_button', $button);

        $onclick2 = "submitAndReloadArea($('ax_invoice_id_block'), '".$this->getAxInvoiceIdSubmitUrl()."')";
        $button2 = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('sales')->__('Save'),
                'class'   => 'save',
                'onclick' => $onclick2
            ));
        $this->setChild('ax_invoice_button', $button2);

        return parent::_prepareLayout();
    }

    /**
     * Checks if we're using guest IDs or not
     *
     * @return bool
     */
    public function isUsingGuestAxIds()
    {
        if ($this->isEeus()) {
            $order = $this->getOrder();
            $customerId = $order->getCustomerId();
            $payment = $order->getPayment();
            if (empty($customerId) && $payment->getMethod() === 'sfc_cybersource') {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the appropriate order's AX account ID
     *
     * @return int|null
     */
    public function getAxAccountId()
    {
        $axId = '';

        if ($this->isEeus()) {
            $axId = Mage::helper('sdm_ax')->getEeusAxAccountId($this->getOrder());
        } else {
            $customer = Mage::getModel('customer/customer')
                ->load($this->getOrder()->getCustomerId());
            $axId = $customer->getAxCustomerId();
        }

        return $axId;
    }

    /**
     * Returns the appropriate order's invoice AX account ID
     *
     * @return int|null
     */
    public function getAxInvoiceId()
    {
        $axId = '';

        if ($this->isEeus()) {
            $axId = Mage::helper('sdm_ax')->getEeusInvoiceAccountId($this->getOrder());
        } else {
            $customer = Mage::getModel('customer/customer')
                ->load($this->getOrder()->getCustomerId());
            $axId = $customer->getAxInvoiceId();
        }

        return $axId;
    }

    /**
     * Returns the action URL to update AX account ID
     *
     * @return string
     */
    public function getAxAcountIdSubmitUrl()
    {
        return $this->getUrl('*/*/saveAxAccountId', array('order_id'=>$this->getOrder()->getId()));
    }

    /**
     * Returns the action URL to update AX invoice account ID
     *
     * @return string
     */
    public function getAxInvoiceIdSubmitUrl()
    {
        return $this->getUrl('*/*/saveAxInvoiceId', array('order_id'=>$this->getOrder()->getId()));
    }

    /**
     * Retrieve available order
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->hasOrder()) {
            return $this->getData('order');
        }
        if (Mage::registry('current_order')) {
            return Mage::registry('current_order');
        }
        if (Mage::registry('order')) {
            return Mage::registry('order');
        }
        Mage::throwException(Mage::helper('sales')->__('Cannot get order instance'));
    }

    /**
     * Returns true if the current order belong to EEUS.
     *
     * Note: Method is made to work with the EEUS store code, not the website code.
     *
     * @return boolean
     */
    public function isEeus()
    {
        if (!isset($this->_isEeus)) {
            $code = Mage::getModel('core/store')->load($this->getOrder()->getStoreId())->getCode();
            if ($code === SDM_Core_Helper_Data::STORE_CODE_EE) {
                $this->_isEeus = true;
            } else {
                $this->_isEeus = false;
            }
        }

        return $this->_isEeus;
    }
}
