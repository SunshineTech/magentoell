<?php
/**
 * Separation Degrees One
 *
 * Manages the retailer application
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_RetailerApplication
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_RetailerApplication_Adminhtml_RetailerapplicationController class
 */
class SDM_RetailerApplication_Adminhtml_RetailerapplicationController extends Mage_Adminhtml_Controller_Action
{
    /**
     * ACL
     *
     * @return void
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/customer/retailerapplication');
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return Mage_Adminhtml_Sales_OrderController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('customer/retailerapplication');

        return $this;
    }

    /**
     * Retailer Applications grid
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title($this->__('Retailer Applications'));
        $this->_initAction()->renderLayout();
    }
}
