<?php
/**
 * Separation Degrees One
 *
 * Lyris Newsletter Management
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Lyris
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Previous Newsletter Banners
 */
class SDM_Lyris_Block_Adminhtml_Ads
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize grid
     */
    public function __construct()
    {
        $this->_controller      = 'adminhtml_ads';
        $this->_blockGroup      = 'sdm_lyris';
        $this->_headerText      = Mage::helper('sdm_lyris')->__('Manage Previous Email Thumbnails');
        $this->_addButtonLabel  = Mage::helper('sdm_lyris')->__('Add Previous Email Thumbnails');
        parent::__construct();
    }
}
