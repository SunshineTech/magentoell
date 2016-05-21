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
 * SDM_RetailerApplication_Block_Checkapplication class
 */
class SDM_RetailerApplication_Block_Checkapplication extends Mage_Core_Block_Template
{
    /**
     * Make sure we have a valid retailer application if we're on the retailer site.
     * If not, redirect to the application page and show a notice.
     *
     * @return void
     */
    protected function _construct()
    {
        if (Mage::helper('sdm_core')->isSite(SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_RE)) {
            $session = Mage::getSingleton('customer/session');
            if (!$session->isLoggedIn() || !$session->getCustomer()->isApprovedRetailer()) {
                Mage::getSingleton('core/session')
                    ->addNotice('An approved retailer application is required to access the cart and/or checkout functionality of this website.');
                $url = Mage::getUrl('retailerapplication/application/view');
                Mage::app()->getResponse()->setRedirect($url)->sendResponse();
                return;
            }
        }
        return parent::_construct();
    }
}
