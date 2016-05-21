<?php
/**
 * Separation Degrees One
 *
 * eCal Lite download request extension
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_EcalLite
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_EcalLite_Adminhtml_EcalliteController class
 */
class SDM_EcalLite_Adminhtml_EcalliteController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout()->_setActiveMenu('report/ecallite');
        $this->_title($this->__('Catalog'))->_title($this->__('View eCal Lite Requests'));

        $this->renderLayout();
    }

    /**
     * Creates the CSV file
     *
     * @return void
     */
    public function exportCsvAction()
    {
        $fileName = 'ecalite_requests.csv';
        $grid = $this->getLayout()->createBlock('ecallite/adminhtml_request_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     * ACL check
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/report/ecallite');
    }
}
