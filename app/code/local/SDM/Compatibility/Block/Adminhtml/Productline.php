<?php
/**
 * Separation Degrees Media
 *
 * Implements the product compatibility functionality.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Compatibility
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Compatibility_Block_Adminhtml_Productline class
 */
class SDM_Compatibility_Block_Adminhtml_Productline extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize
     */
    public function __construct()
    {
        // The blockGroup must match the first half of how we call the block, and controller matches the second half
        // ie. foo_bar/adminhtml_baz
        $this->_blockGroup = 'compatibility';
        $this->_controller = 'adminhtml_productline';
        $this->_headerText = $this->__('Product Line');

        parent::__construct();

        $this->_updateButton(   // Need custom action
            'add',
            'onclick',
            'setLocation(\'' . $this->getUrl('*/*/pnew') .'\')'
        );
    }
}
