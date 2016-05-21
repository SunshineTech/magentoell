<?php
/**
 * Separation Degrees One
 *
 * eClips Software Download
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Eclips
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Eclips_Adminhtml_EclipsController class
 */
class SDM_Eclips_Adminhtml_EclipsController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout()->_setActiveMenu('report/eclips');
        $this->_title($this->__('Catalog'))->_title($this->__('View eClips Download Request'));

        $this->renderLayout();
    }

    /**
     * ACL check
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/report/eclips');
    }
}
