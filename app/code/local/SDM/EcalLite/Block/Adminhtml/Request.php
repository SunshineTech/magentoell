<?php
/**
 * Separation Degrees One
 *
 * eCal Lite download request extension
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_EcalLite
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_EcalLite_Block_Adminhtml_Request class
 */
class SDM_EcalLite_Block_Adminhtml_Request
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize
     */
    public function __construct()
    {
        $this->_blockGroup = 'ecallite';
        $this->_controller = 'adminhtml_request';  // Path to this class
        $this->_headerText = $this->__('View eCal Lite Requests');

        parent::__construct();

        $this->_removeButton('add');
    }
}
