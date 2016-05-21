<?php
/**
 * Separation Degrees One
 *
 * Implements the customer/retailer discount logic for viewing and obtaining
 * discounts and prices, as wel as managing the customer groups.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CustomerDiscount
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_CustomerDiscount_Block_Adminhtml_Discountgroup class
 */
class SDM_CustomerDiscount_Block_Adminhtml_Discountgroup extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize
     */
    public function __construct()
    {
        $this->_blockGroup = 'customerdiscount';
        $this->_controller = 'adminhtml_discountgroup'; // path to this class
        $this->_headerText = $this->__('Retailer Discount');

        parent::__construct();

        $this->_updateButton(   // Need custom action
            'add',
            'onclick',
            'setLocation(\'' . $this->getUrl('*/*/new') .'\')'
        );
    }
}
