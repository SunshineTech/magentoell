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
 * SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator class
 */
class SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_gmapstrlocator';
        $this->_blockGroup = 'gmapstrlocator';
        $this->_headerText = Mage::helper('gmapstrlocator')->__('G-Map Store Locator Manager');
        $this->_addButtonLabel = Mage::helper('gmapstrlocator')->__('Add New Store');
        parent::__construct();
    }
}
