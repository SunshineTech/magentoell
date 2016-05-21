<?php
/**
 * Separation Degrees One
 *
 * Ellison's Mage_Customer customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Customer
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Customer_Block_Widget_Company class
 */
class SDM_Customer_Block_Widget_Company extends Mage_Customer_Block_Widget_Abstract
{
    /**
     * Initialize block
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('sdm/customer/widget/company.phtml');
    }

    /**
     * Check if gender attribute enabled in system
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->_getAttribute('company')->getIsVisible();
    }

    /**
     * Check if gender attribute marked as required
     *
     * @return bool
     */
    public function isRequired()
    {
        return (bool)$this->_getAttribute('company')->getIsRequired();
    }

    /**
     * Get current customer from session
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * Retrieve store attribute label
     *
     * @return string
     */
    public function getStoreLabel()
    {
        $attribute = $this->_getAttribute('company');
        return $attribute ? $this->__($attribute->getStoreLabel()) : '';
    }
}
