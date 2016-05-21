<?php
/**
 * Separation Degrees Media
 *
 * Allows saving quotes that can be later be converted into orders with preserved
 * pricing.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_SavedQuote
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_SavedQuote_Model_Savedquote_Address
 */
class SDM_SavedQuote_Model_Savedquote_Address extends Mage_Core_Model_Abstract
{

    /**
     * Directory country models
     *
     * @var array
     */
    static protected $_countryModels = array();

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('savedquote/savedquote_address');
    }

    /**
     * Backend validation using Zend_Validate
     *
     * @see    Mage_Customer_Model_Address_Abstract::_basicCheck()
     * @throws Mage_Core_Exception
     *
     * @return SDM_SavedQuote_Model_Savedquote_Address
     */
    public function validate()
    {
        $errors = array();

        if (!Zend_Validate::is(trim($this->getFirstname()), 'NotEmpty')) {
            $errors[] = Mage::helper('savedquote')->__('The first name cannot be empty.');
        }
        if (!Zend_Validate::is(trim($this->getLastname()), 'NotEmpty')) {
            $errors[] = Mage::helper('savedquote')->__('The last name cannot be empty.');
        }
        if (!Zend_Validate::is($this->getStreet(), 'NotEmpty')) {
            $errors[] = Mage::helper('savedquote')->__('Please enter the street.');
        }
        if (!Zend_Validate::is($this->getCity(), 'NotEmpty')) {
            $errors[] = Mage::helper('savedquote')->__('Please enter the city.');
        }
        if (!Zend_Validate::is($this->getTelephone(), 'NotEmpty')) {
            $errors[] = Mage::helper('savedquote')->__('Please enter the telephone number.');
        }
        if (!Zend_Validate::is($this->getRegion(), 'NotEmpty')) {
            $errors[] = Mage::helper('savedquote')->__('Please enter the state.');
        }
        // These are already readonly in the form
        //         if ($this->getCountryModel()->getRegionCollection()->getSize()
        //             && !Zend_Validate::is($this->getRegionId(), 'NotEmpty')
        //             && Mage::helper('directory')->isRegionRequired($this->getCountryId())
        //         ) {
        //             $errors[] = Mage::helper('savedquote')->__('Please enter the state/province.');
        //         }
        //         $_havingOptionalZip = Mage::helper('directory')->getCountriesWithOptionalZip();
        // Mage::log($_havingOptionalZip);
        //         if (!in_array($this->getCountryId(), $_havingOptionalZip)
        //             && !Zend_Validate::is($this->getPostcode(), 'NotEmpty')
        //         ) {
        //             $errors[] = Mage::helper('savedquote')->__('Please enter the zip/postal code.');
        //         }

        if (empty($errors)) {
            return $this;
            // return $this->save();
        }

        Mage::throwException('Address validation failed. ' . implode(' ', $errors));
    }

    /**
     * Retrive country model
     *
     * @return Mage_Directory_Model_Country
     */
    public function getCountryModel()
    {
        if (!isset(self::$_countryModels[$this->getCountryId()])) {
            self::$_countryModels[$this->getCountryId()] = Mage::getModel('directory/country')
                ->load($this->getCountryId());
        }

        return self::$_countryModels[$this->getCountryId()];
    }
}
