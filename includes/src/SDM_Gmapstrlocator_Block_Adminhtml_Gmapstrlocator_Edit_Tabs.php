<?php
/**
 * Separation Degrees Media
 *
 * This module handles all the store locator functionality for Ellison.
 *
 * The original code from this module is based off the FME_Gmapstrlocator module. We converted their
 * module to an SDM module rather than extending from it because the amount of modifications and
 * rewrites necessary for it to fit Ellison's spec were extensive, yet we still felt there was value
 * in using FME's module as a starting point.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Gmapstrlocator
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator_Edit_Tabs class
 */
class SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator_Edit_Tabs
    extends Mage_Adminhtml_Block_Widget_Tabs
{

    /**
     * Initialize
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('gmapstrlocator_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('gmapstrlocator')->__('Store Information'));
    }

    /**
     * Add tab
     *
     * @return SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('gmapstrlocator')->__('Store Information'),
            'title'     => Mage::helper('gmapstrlocator')->__('Store Information'),
            'content'   => $this->getLayout()->createBlock('gmapstrlocator/adminhtml_gmapstrlocator_edit_tab_form')
                ->toHtml(),
            ));

        return parent::_beforeToHtml();
    }
}
