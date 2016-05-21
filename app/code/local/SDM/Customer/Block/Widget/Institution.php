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
 * SDM_Customer_Block_Widget_Institution class
 */
class SDM_Customer_Block_Widget_Institution extends Mage_Customer_Block_Widget_Abstract
{
    /**
     * Initialize block
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('sdm/customer/widget/institution.phtml');
    }

    /**
     * Check if gender attribute enabled in system
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->_getAttribute('institution')->getIsVisible();
    }

    /**
     * Check if gender attribute marked as required
     *
     * @return bool
     */
    public function isRequired()
    {
        return (bool)$this->_getAttribute('institution')->getIsRequired();
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
        $attribute = $this->_getAttribute('institution');
        return $attribute ? $this->__($attribute->getStoreLabel()) : '';
    }
}
