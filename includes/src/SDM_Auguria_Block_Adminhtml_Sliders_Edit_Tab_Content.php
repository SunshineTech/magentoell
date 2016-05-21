<?php
/**
 * Separation Degrees One
 *
 * Updates to Auguria_Sliders
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Auguria
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Auguria_Block_Adminhtml_Sliders_Edit_Tab_Content
 */
class SDM_Auguria_Block_Adminhtml_Sliders_Edit_Tab_Content
    extends Auguria_Sliders_Block_Adminhtml_Sliders_Edit_Tab_Content
{
    /**
     * Prepare form
     *
     * @return this
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('sliders_form', array('legend'=>Mage::helper('auguria_sliders')->__("Content")));
        
        $fieldset->addField('slider_id', 'hidden', array(
                'name'      => 'id',
        ));
        
        $fieldset->addField('name', 'text', array(
                'label'     => Mage::helper('auguria_sliders')->__('Name'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'name',
        ));

        $afterElementHtml = '<p class="nm" style="color:green;" ><small>' .
            '( Image Size should be 2400 X 650 in px. )' . '</small></p>';
        $fieldset->addField('image', 'image', array(
                'label'     => Mage::helper('auguria_sliders')->__('Image'),
                'required'  => false,
                'name'      => 'image',
                'after_element_html'=>$afterElementHtml,
        ));
        
        $afterElementHtml = '<p class="nm" style="color:green;" ><small>' .
            '( Image Size should be 700 X 500 in px. )' . '</small></p>';
        $fieldset->addField('image_mobile', 'image', array(
                'label'     => Mage::helper('auguria_sliders')->__('Image (Mobile)'),
                'required'  => false,
                'name'      => 'image_mobile',
                'after_element_html'=>$afterElementHtml,
        ), 'image');

        $fieldset->addField('link', 'text', array(
                'label'     => Mage::helper('auguria_sliders')->__('Link'),
                'required'  => false,
                'name'      => 'link',
        ));
        
        $config = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
        $config->setData(
            'files_browser_window_url',
            Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index/')
        );
        $config->setData(
            'directives_url',
            Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive')
        );
        $config->setData(
            'directives_url_quoted',
            preg_quote($config->getData(
            'directives_url'))
        );
        $config->setData(
            'widget_window_url',
            Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/widget/index')
        );
        
        $fieldset->addField('cms_content', 'editor', array(
                'label'     => Mage::helper('auguria_sliders')->__('Cms content'),
                'required'  => false,
                'name'      => 'cms_content',
                'config'    => $config
        ));
        
        $fieldset->addField('sort_order', 'text', array(
                'label'     => Mage::helper('auguria_sliders')->__('Sort order'),
                'required'  => false,
                'name'      => 'sort_order',
                'class' => 'validate-digits'
        ));
        
        $status = Mage::helper('auguria_sliders')->getIsActiveOptionArray();
        array_unshift($status, array('label'=>'', 'value'=>''));
        $fieldset->addField('is_active', 'select', array(
                'label'     => Mage::helper('auguria_sliders')->__('Status'),
                'required'  => true,
                'name'      => 'is_active',
                'values'    => $status
        ));

        if (Mage::getSingleton('adminhtml/session')->getSlidersData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getSlidersData());
            Mage::getSingleton('adminhtml/session')->setSlidersData(null);
        } elseif (Mage::registry('sliders_data')) {
            $form->setValues(Mage::registry('sliders_data')->getData());
        }

        return $this;
    }
}
