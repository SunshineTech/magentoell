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
 * SDM_RetailerApplication_Block_Adminhtml_Retailerapplication class
 */
class SDM_RetailerApplication_Block_Adminhtml_Retailerapplication
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Construct the grid container
     */
    public function __construct()
    {
        $this->_blockGroup = 'retailerapplication';
        $this->_controller = 'adminhtml_retailerapplication';
        $this->_headerText = Mage::helper('retailerapplication')->__('Retailer Applications');

        parent::__construct();
        $this->_removeButton('add');
    }
}
