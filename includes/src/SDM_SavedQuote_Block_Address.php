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
 * SDM_SavedQuote_Block_Address class
 */
class SDM_SavedQuote_Block_Address
    extends Mage_Checkout_Block_Onepage_Abstract
{
    /**
     * Returns the saved quote ID
     *
     * @return int
     */
    public function getSavedQuoteId()
    {
        return Mage::registry('saved_quote')->getId();
    }

    /**
     * Returns the shipping address
     *
     * @param array $data Address data in a specific format
     *
     * @return SDM_SavedQuote_Model_Savedquote_Address
     */
    public function getShippingAddress($data = null)
    {
        $address = Mage::registry('saved_quote')->getShippingAddress();

        // If additional data is available, set it to the address object
        if ($data) {
            if (isset($data['street'])) {
                $address->setStreet(implode("\n", $data['street']));
            }
            if (isset($data['firstname'])) {
                $address->setFirstname($data['firstname']);
            }
            if (isset($data['lastname'])) {
                $address->setLastname($data['lastname']);
            }
            if (isset($data['company'])) {
                $address->setCompany($data['company']);
            }
            if (isset($data['city'])) {
                $address->setCity($data['city']);
            }
            if (isset($data['telephone'])) {
                $address->setTelephone($data['telephone']);
            }
            if (isset($data['fax'])) {
                $address->setFax($data['fax']);
            }
        }

        return $address;
    }

    /**
     * Returns the billing address
     *
     * @return SDM_SavedQuote_Model_Savedquote_Address
     */
    public function getBillingAddress()
    {
        $address = Mage::registry('saved_quote')->getBillingAddress();

        return $address;
    }

    /**
     * Get specifically the saved quote's shipping address' country ID
     *
     * @param str $type
     *
     * @return str
     */
    public function getCountryHtmlSelect($type)
    {
        $countryId = $this->getShippingAddress()->getCountryId();

        if (is_null($countryId)) {
            $countryId = Mage::helper('core')->getDefaultCountry();
        }
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[country_id]')
            ->setId($type.':country_id')
            ->setTitle(Mage::helper('savedquote')->__('Country'))
            ->setClass('validate-select')
            ->setValue($countryId)
            ->setOptions($this->getCountryOptions());

        return $select->getHtml();
    }

    /**
     * Get country name by 2 character code
     *
     * @param string $id
     *
     * @return string
     */
    public function getCountyNameById($id)
    {
        if ($id) {
            return Mage::getModel('directory/country')->loadByCode($id)->getName();
        }
    }

    /**
     * Return the select HTML for the US region dropdown menu
     *
     * @param str          $type
     * @param null|integer $selectId
     *
     * @return str
     */
    public function getUsRegionHtmlSelect($type, $selectId = null)
    {
        // $regions = $this->getRegionArray();
        $regions = $this->getUsRegionCollection()->toOptionArray();    // missing acronyms

        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[region_id]')
            ->setId($type.':region_id')
            ->setTitle(Mage::helper('savedquote')->__('State/Province'))
            ->setClass('required-entry validate-state')
            ->setValue('US')
            ->setOptions($regions);

        $html = $select->getHtml();

        // Select an option if given
        if ($selectId) {
            $selectId = (int)$selectId;
            $html = str_replace(
                "<option value=\"$selectId\"",
                "<option value=\"$selectId\" selected",
                $html
            );
        }

        return $html;
    }

    /**
     * Returns a collection of US regions
     *
     * @return Mage_Directory_Model_Resource_Region_Collection
     */
    public function getUsRegionCollection()
    {
        if (!$this->_regionCollection) {
            $this->_regionCollection = Mage::getModel('directory/region')->getResourceCollection()
                ->addCountryFilter('US')
                ->load();
        }
        return $this->_regionCollection;
    }

    /**
     * Returns an array of US region array
     *
     * @return array
     */
    public function getRegionArray()
    {
        $regions = array();
        $collection = Mage::getModel('directory/region')->getResourceCollection()
            ->addCountryFilter('US');

        $regions[0] = array(
            'title' => '',
            'value' => '',
            'label' => '-- Please select --'
        );

        foreach ($collection as $region) {
            $regions[] = array(
                'title' => $region->getDefaultName(),
                'value' => $region->getCode(),
                'label' => $region->getDefaultName()
            );
        }

        return $regions;
    }

    /**
     * Get address HTML select
     *
     * @param  string $type
     * @return string
     */
    public function getAddressesHtmlSelect($type)
    {
        if ($this->isCustomerLoggedIn()) {
            $options = array();
            $addressFillData = array();
            $currentAddress = $this->getShippingAddress();
            foreach ($this->getCustomer()->getAddresses() as $address) {
                // Ensure country matches
                if ($currentAddress->getCountryId() !== $address->getCountryId()) {
                    continue;
                }
                // Ensure postcode matches
                if ($currentAddress->getPostcode() !== $address->getPostcode()) {
                    continue;
                }
                // Ensure region matches
                if ((string)$currentAddress->getRegionId() !== (string)$address->getRegionId()) {
                    continue;
                }
                // Add to options
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $address->format('oneline')
                );
                // Add address fields
                $streets = $address->getStreet();
                $addressFillData[$address->getId()] = array(
                    'firstname' => $address->getFirstname(),
                    'lastname'  => $address->getLastname(),
                    'company'   => $address->getCompany(),
                    'street1'   => $streets[0],
                    'street2'   => isset($streets[1]) ? $streets[1] : '',
                    'city'      => $address->getCity(),
                    'telephone' => $address->getTelephone(),
                    'fax'       => $address->getFax()
                );
            }

            // No matching options, show nothing
            if (empty($options)) {
                return '';
            }

            $select = $this->getLayout()->createBlock('core/html_select')
                ->setName($type.'_address_id')
                ->setId($type.'-address-select')
                ->setClass('address-select')
                ->setValue('')
                ->setOptions($options);

            $select->addOption('', Mage::helper('checkout')->__('-- Use New Address --'));

            $addressFillData = Mage::helper('core')->jsonEncode($addressFillData);
            $script  = "<script>var addressFillData = " . $addressFillData . ";</script>";

            return $select->getHtml() . $script;
        }
        return '';
    }
}
