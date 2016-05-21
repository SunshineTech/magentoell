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
 * SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator_Renderer_Store class
 */
class SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator_Renderer_Store
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders the website ID column of the grid
     *
     * @param Varien Object $row
     *
     * @return str HTML
     */
    public function render(Varien_Object $row)
    {
        $rawStr = trim($row->getStoreType());
        $stores = explode('|', $rawStr);
        $temp = array();
        $options = SDM_Gmapstrlocator_Model_System_Config_Source_Storetypes::toOptionArray();

        foreach ($stores as $store) {
            if ($store) {
                $temp[] = $options[$store];
            }
        }

        return implode(', ', $temp);
    }
}