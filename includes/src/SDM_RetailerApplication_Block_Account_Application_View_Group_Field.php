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
 * SDM_RetailerApplication_Block_Account_Application_View_Group_Field class
 */
class SDM_RetailerApplication_Block_Account_Application_View_Group_Field
    extends Mage_Core_Block_Template
{
    /**
     * Gets a singleton of the current logged in user's application
     *
     * @return SDM_RetailerApplication_Model
     */
    public function getCurrentApplication()
    {
        $application = Mage::getSingleton('retailerapplication/application');
        if (!$application->getId()) {
            $application->loadCurrentCustomer();
        }
        return $application;
    }

    /**
     * Returns the values array
     *
     * @return array
     */
    public function getValues()
    {
        return $this->getData('values');
    }

    /**
     * Gets the current value; if the value===fax, then returns nothing
     *
     * @return string
     */
    public function getValue()
    {
        if ($this->getFax()) {
            return '';
        }
        return $this->escapeHtml($this->getCurrentApplication()->getData($this->getId()));
    }

    /**
     * Checks if the value of the field is fax
     *
     * @return bool
     */
    public function getFax()
    {
        return $this->escapeHtml($this->getCurrentApplication()->getData($this->getId())) === 'fax';
    }

    /**
     * Returns the address for this field's address type
     *
     * @return Mage_Customer_Model_Address
     */
    public function getAddress()
    {
        return $this->getCurrentApplication()->getAddress(
            $this->getAddressType()
        );
    }

    /**
     * Returns the type of address we are rendering (shipping, billing, owner)
     *
     * @return string
     */
    public function getAddressType()
    {
        $explodedId = explode('_', $this->getId());
        return reset($explodedId);
    }

    /**
     * Returns the HTML for the country select
     *
     * @param  string $type
     * @return string
     */
    public function getCountryHtmlSelect($type)
    {
        $countryId = $this->getAddress()->getCountryId();

        if (is_null($countryId)) {
            $countryId = Mage::helper('core')->getDefaultCountry();
        }
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[country_id]')
            ->setId($type.':country')
            ->setTitle(Mage::helper('savedquote')->__('Country'))
            ->setClass('validate-select')
            ->setValue($countryId)
            ->setOptions($this->getCountryOptions());

        return $select->getHtml();
    }

    /**
     * Get's the possible country options
     *
     * @return array
     */
    public function getCountryOptions()
    {
        $options    = false;
        $useCache   = Mage::app()->useCache('config');
        if ($useCache) {
            $cacheId    = 'DIRECTORY_COUNTRY_SELECT_STORE_' . Mage::app()->getStore()->getCode();
            $cacheTags  = array('config');
            if ($optionsCache = Mage::app()->loadCache($cacheId)) {
                $options = unserialize($optionsCache);
            }
        }

        if ($options == false) {
            $options = $this->getCountryCollection()->toOptionArray();
            if ($useCache) {
                Mage::app()->saveCache(serialize($options), $cacheId, $cacheTags);
            }
        }
        return $options;
    }

    /**
     * Returns a collection of countries
     *
     * @return Mage_Directory_Model_Country_Resource_Collection
     */
    public function getCountryCollection()
    {
        if (!$this->_countryCollection) {
            $this->_countryCollection = Mage::getSingleton('directory/country')->getResourceCollection()
                ->loadByStore();
        }
        return $this->_countryCollection;
    }
}
