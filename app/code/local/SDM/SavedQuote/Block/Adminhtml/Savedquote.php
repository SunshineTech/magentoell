<?php
/**
 * Separation Degrees Media
 *
 * Allows saving quotes that can be later be converted into orders with preserved
 * pricing.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_SavedQuote
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_SavedQuote_Block_Adminhtml_Savedquote class
 */
class SDM_SavedQuote_Block_Adminhtml_Savedquote
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize
     */
    public function __construct()
    {
        $this->_blockGroup = 'savedquote';  // Name (i.e. 'blockGroup_here/controller_here')
        $this->_controller = 'adminhtml_savedquote';    // Basically path to this class
        $this->_headerText = Mage::helper('savedquote')->__('Saved Quotes');

        parent::__construct();
        $this->_removeButton('add');
    }
}
