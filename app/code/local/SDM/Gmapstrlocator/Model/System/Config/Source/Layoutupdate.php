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
 * SDM_Gmapstrlocator_Model_System_Config_Source_Layoutupdate class
 */
class SDM_Gmapstrlocator_Model_System_Config_Source_Layoutupdate
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'one_column', 'label'=>Mage::helper('adminhtml')->__('1 column')),
            array('value' => 'two_columns_left', 'label'=>Mage::helper('adminhtml')->__('2 columns with left bar')),
            array('value' => 'two_columns_right', 'label'=>Mage::helper('adminhtml')->__('2 columns with right bar')),
            array('value' => 'three_columns', 'label'=>Mage::helper('adminhtml')->__('3 columns')),
        );
    }
}
