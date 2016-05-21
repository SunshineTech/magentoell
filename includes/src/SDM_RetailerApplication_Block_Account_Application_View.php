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
 * SDM_RetailerApplication_Block_Account_Application_View class
 */
class SDM_RetailerApplication_Block_Account_Application_View extends Mage_Core_Block_Template
{
    /**
     * Gets the current application singleton
     *
     * @return SDM_RetailerApplication_Model_Application
     */
    public function getApplication()
    {
        return Mage::helper('retailerapplication')->getCurrentApplication();
    }

    /**
     * Get's the field groups to generate the form
     *
     * @return array
     */
    public function getFrontendFieldGroups()
    {
        return Mage::helper('retailerapplication/fields')->getFrontendFieldGroups();
    }

    /**
     * Returns the submit URL for the form
     *
     * @return string
     */
    public function getFormAction()
    {
        return Mage::getUrl('retailerapplication/application/save');
    }
}
