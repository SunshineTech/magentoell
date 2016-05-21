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
 * SDM_Compatibility_Block_Adminhtml_Compatibility class
 */
class SDM_Compatibility_Block_Adminhtml_Compatibility extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize
     */
    public function __construct()
    {
        $this->_blockGroup = 'compatibility';
        $this->_controller = 'adminhtml_compatibility';
        $this->_headerText = $this->__('Compatibility');

        parent::__construct();
    }
}
