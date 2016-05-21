<?php
/**
 * Separation Degrees Media
 *
 * Ellison's custom Landing Page Management System (LPMS).
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Lpms
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Lpms_Block_Adminhtml_Cms_Page_Edit_Tab_Main class
 */
class SDM_Lpms_Block_Adminhtml_Cms_Page_Edit_Tab_Main
    extends Mage_Adminhtml_Block_Cms_Page_Edit_Tab_Main
{
    /**
     * Prepare form
     *
     * @return SDM_Lpms_Block_Adminhtml_Cms_Page_Edit_Tab_Main
     */
    protected function _prepareForm()
    {
        $return = parent::_prepareForm();
        
        $model = Mage::registry('cms_page');
        
        $helper = Mage::helper('lpms');

        $baseFieldset = $this->getForm()->getElement('base_fieldset');
        $baseFieldset->addField('type', 'select', array(
            'name'      => 'type',
            'label'     => Mage::helper('cms')->__('Type'),
            'title'     => Mage::helper('cms')->__('Type'),
            'required'  => true,
            'values'    => $helper->getPageTypes()
        ));

        $baseFieldset->addField('taxonomy_id', 'select', array(
            'name'      => 'taxonomy_id',
            'label'     => Mage::helper('cms')->__('Taxonomy Item'),
            'title'     => Mage::helper('cms')->__('Taxonomy Item'),
            'required'  => false,
            'values'    => $helper->getTaxonomyItemsForCmsPage(),
            'after_element_html' =>  '<p class="note"><span>Relates a Designer to this Designer Article</span></p>'
        ));

        $this->getForm()->setValues($model->getData());

        return $return;
    }
}
