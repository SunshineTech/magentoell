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
 * SDM_Gmapstrlocator_Model_System_Config_Source_Countrylist class
 */
class SDM_Gmapstrlocator_Model_System_Config_Source_Countrylist
{
    /**
     * Options getter
     *
     * @param boolean $grid
     *
     * @return array
     */
    public function toOptionArray($grid = false)
    {
        $collection = Mage::getModel('directory/country')->getCollection();
        $options = '';
        if ($grid) {
            foreach ($collection as $country) {
                $options[$country->getName()] = Mage::helper('adminhtml')->__($country->getName());
            }
        } else {
            $options[] = array('value'=>'','label'=>'');
            foreach ($collection as $country) {
                $options[] = array(
                    'value' => $country->getName(),
                    'label' => Mage::helper('adminhtml')->__($country->getName())
                );
            }
        }
        array_multisort($options, SORT_ASC);
        return $options;
    }
}
