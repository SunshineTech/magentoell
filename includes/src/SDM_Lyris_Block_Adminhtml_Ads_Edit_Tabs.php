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
 * Edit Ads Tabs
 */
class SDM_Lyris_Block_Adminhtml_Ads_Edit_Tabs
    extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Setup Tabs
     */
    public function __construct()
    {
        parent::__construct();
        $this
            ->setId('ads_tabs')
            ->setDestElementId('edit_form')
            ->setTitle(Mage::helper('sdm_lyris')->__('Information'));
    }

    /**
     * Create Tab
     * @return SDM_Lyris_Block_Adminhtml_Ads_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('sdm_lyris')->__('Thumbnail'),
            'title'     => Mage::helper('sdm_lyris')->__('Thumbnail'),
            'content'   => $this->getLayout()->createBlock('sdm_lyris/adminhtml_ads_edit_tab_form')->toHtml(),
            ));
        return parent::_beforeToHtml();
    }
}
