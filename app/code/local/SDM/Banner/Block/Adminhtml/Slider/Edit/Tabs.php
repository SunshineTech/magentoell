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
 * SDM_Banner_Block_Adminhtml_Slider_Edit_Tabs class
 */
class SDM_Banner_Block_Adminhtml_Slider_Edit_Tabs
    extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('slider_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('slider')->__('Banner Information'));
    }

    /**
     * Add tabs
     *
     * @return SDM_Banner_Block_Adminhtml_Slider_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('slider')->__('Banner Information'),
            'title'     => Mage::helper('slider')->__('Banner Information'),
            'content'   => $this->getLayout()->createBlock('slider/adminhtml_slider_edit_tab_form')->toHtml(),
            ));
        $this->addTab('pages_section', array(
            'label'     => Mage::helper('slider')->__('Pages'),
            'title'     => Mage::helper('slider')->__('Pages'),
            'content'   => $this->getLayout()->createBlock('slider/adminhtml_slider_edit_tab_pages')->toHtml(),
        ));
        $this->addTab('stores_section', array(
            'label'     => Mage::helper('slider')->__('Websites'),
            'title'     => Mage::helper('slider')->__('Websites'),
            'content'   => $this->getLayout()->createBlock('slider/adminhtml_slider_edit_tab_stores')->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}
