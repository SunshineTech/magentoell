<?php
/**
 * Separation Degrees Media
 *
 * This module handles all the store locator functionality for Ellison.
 *
 * The original code from this module is based off the FME_Gmapstrlocator module. We converted their
 * module to an SDM module rather than extending from it because the amount of modifications and
 * rewrites necessary for it to fit Ellison's spec were extensive, yet we still felt there was value
 * in using FME's module as a starting point.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Gmapstrlocator
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Gmapstrlocator_Block_Gmapstrlocator class
 */
class SDM_Gmapstrlocator_Block_Gmapstrlocator extends Mage_Core_Block_Template
{
    protected $_countryOptionsForLocator = null;
    protected $_defaultCountry = null;

    /**
     * Prepare layout
     *
     * @return SDM_Gmapstrlocator_Block_Gmapstrlocator
     */
    public function _prepareLayout()
    {
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->setTitle(Mage::helper('gmapstrlocator')->getGMapPageTitle());
        }
        return parent::_prepareLayout();
    }

    /**
     * Get store locator
     *
     * @return mixed
     */
    public function getGmapstrlocator()
    {
        if (!$this->hasData('gmapstrlocator')) {
            $this->setData('gmapstrlocator', Mage::registry('gmapstrlocator'));
        }
        return $this->getData('gmapstrlocator');
    }

    /**
     * Get country html select
     *
     * @param integer $elementId
     *
     * @return string
     */
    public function getCountryHtmlSelect($elementId)
    {
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setId($elementId)
            ->setTitle(Mage::helper('core')->__('Country'))
            ->setValue($this->_getDefaultCountry())
            ->setOptions($this->_getCountryOptionsForLocator());

        return $select->getHtml();
    }

    /**
     * Get options
     *
     * @return mixed
     */
    protected function _getCountryOptionsForLocator()
    {
        if ($this->_countryOptionsForLocator === null) {
            $this->_generateCountryOptions();
        }
        return $this->_countryOptionsForLocator;
    }

    /**
     * Default country
     *
     * @return mixed
     */
    protected function _getDefaultCountry()
    {
        if ($this->_defaultCountry === null) {
            $this->_generateCountryOptions();
        }
        return $this->_defaultCountry;
    }

    /**
     * Generate options
     *
     * @return void
     */
    protected function _generateCountryOptions()
    {
        if ($this->_countryOptionsForLocator === null || $this->_defaultCountry === null) {
            $defaultCountryId = Mage::helper('core')->getDefaultCountry();
            $this->_defaultCountry = '';

            $options = Mage::getSingleton('directory/country')
                ->getResourceCollection()
                ->loadByStore()
                ->toOptionArray();
            unset($options[0]);
            $countryValues = array();
            foreach ($options as $option) {
                if ($defaultCountryId == $option['value']) {
                    $this->_defaultCountry = $option['label'];
                }
                $countryValues[$option['label']] = $option['value'];
            }
            $this->_defaultCountry = empty($this->_defaultCountry) ? $defaultCountryId : $this->_defaultCountry;

            $collection = Mage::getModel('gmapstrlocator/location')->getCollection();
            $collection->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->where('store_type LIKE "%|physical|%" AND status=1')
                ->columns('DISTINCT(country) as country')
                ->order('country ASC');

            $countries = array();
            foreach ($collection as $item) {
                $countries[] = array(
                    'value' => $item->getCountry(),
                    'label' => $item->getCountry()
                );
            }
            $this->_countryOptionsForLocator = $countries;
        }
    }
}
