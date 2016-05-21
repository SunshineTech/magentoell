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
 * Custom source model to allow predefined integers for states
 */
class SDM_Customer_Model_Attribute_Source_Institutiondescription
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'value' => '',
                    'label' => ''
                ),
                array(
                    'value' => 'DA',
                    'label' => Mage::helper('sdm_catalog')
                        ->__('Day Care 3-6 yrs & Afterschool and Summer School')
                ),
                array(
                    'value' => 'DM',
                    'label' => Mage::helper('sdm_catalog')
                        ->__('District Media Center')
                ),
                array(
                    'value' => 'HE',
                    'label' => Mage::helper('sdm_catalog')
                        ->__('Head Start - Even Start')
                ),
                array(
                    'value' => 'IN',
                    'label' => Mage::helper('sdm_catalog')
                        ->__('Individuals, Teachers, Crafters, or Designers')
                ),
                array(
                    'value' => 'NP',
                    'label' => Mage::helper('sdm_catalog')
                        ->__('Non-Profit Organisation Hospitals')
                ),
                array(
                    'value' => 'PL',
                    'label' => Mage::helper('sdm_catalog')
                        ->__('Public Library')
                ),
                array(
                    'value' => 'SC',
                    'label' => Mage::helper('sdm_catalog')
                        ->__('School - Church')
                ),
                array(
                    'value' => 'SD',
                    'label' => Mage::helper('sdm_catalog')
                        ->__('School - District')
                ),
                array(
                    'value' => 'SE',
                    'label' => Mage::helper('sdm_catalog')
                        ->__('School - Elementary')
                ),
                array(
                    'value' => 'SG',
                    'label' => Mage::helper('sdm_catalog')
                        ->__('School - Government, Government Agencies')
                ),
                array(
                    'value' => 'SH',
                    'label' => Mage::helper('sdm_catalog')
                        ->__('School - High School')
                ),
                array(
                    'value' => 'SJ',
                    'label' => Mage::helper('sdm_catalog')
                        ->__('School - Junior High')
                ),
                array(
                    'value' => 'SP',
                    'label' => Mage::helper('sdm_catalog')
                        ->__('School - Pre-School, Early Childhood Centers')
                ),
                array(
                    'value' => 'PR',
                    'label' => Mage::helper('sdm_catalog')
                        ->__('School - Private')
                ),
                array(
                    'value' => 'SCHE',
                    'label' => Mage::helper('sdm_catalog')
                        ->__('School Charter Elementary, Jr High, High ')
                )
            );
        }
        return $this->_options;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = array();
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     *
     * @return string
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}
