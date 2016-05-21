<?php
/**
 * Separation Degrees One
 *
 * Banner Ads
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Banner
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */
/**
 * SDM_Banner_Block_Adminhtml_Slider class
 */
class SDM_Banner_Block_Adminhtml_Slider
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_slider';
        $this->_blockGroup = 'slider';
        $this->_headerText = Mage::helper('slider')->__('Banners Manager');
        $this->_addButtonLabel = Mage::helper('slider')->__('Add Banner');
        parent::__construct();
    }
}
