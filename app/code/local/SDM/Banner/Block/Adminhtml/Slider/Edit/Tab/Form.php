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
 * SDM_Banner_Block_Adminhtml_Slider_Edit_Tab_Form class
 */
class SDM_Banner_Block_Adminhtml_Slider_Edit_Tab_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare the form fields
     *
     * @return SDM_Banner_Block_Adminhtml_Slider_Edit_Tab_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('slider_form', array(
            'legend' => Mage::helper('slider')->__('Banner information')
        ));
        $fieldset->addField('title', 'text', array(
            'label'     => Mage::helper('slider')->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'title',
            'tabindex' => 1
            ));

        $afterElementHtml = '<p class="nm" style="color:green;" ><small>'
            . '( Image Size should be 1000 x 65 in px. )' . '</small></p>';
        $fieldset->addField('sliderimage', 'image', array(
            'label'     => Mage::helper('slider')->__('Desktop Image'),
            'required'  => true,
            'name'      => 'sliderimage',
            'after_element_html'=>$afterElementHtml,
            'tabindex' => 1
            ));

        $afterElementHtml2 = '<p class="nm" style="color:green;" ><small>'
            . '( Image Size should be 770 x 120 in px. )' . '</small></p>';
        $fieldset->addField('mobileimage', 'image', array(
            'label'     => Mage::helper('slider')->__('Mobile Image'),
            'required'  => true,
            'name'      => 'mobileimage',
            'after_element_html'=>$afterElementHtml2,
            'tabindex' => 1
            ));

        $fieldset->addField('bannerurl', 'text', array('label'     => Mage::helper('slider')->__('Banner URL'),
            'required'  => false,
            'name'      => 'bannerurl',
            'class'     => 'validate-url',
            'tabindex' => 1
            ));

        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('slider')->__('Status'),
            'name'      => 'status',
            'values'    => array(
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('slider')->__('Enabled'),
                    ),
                array(
                    'value'     => 2,
                    'label'     => Mage::helper('slider')->__('Disabled'),
                    ),
                ),
            'tabindex' => 1
            ));

        if (Mage::getSingleton('adminhtml/session')->getSliderData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getSliderData());
            Mage::getSingleton('adminhtml/session')->setSliderData(null);
        } elseif (Mage::registry('slider_data')) {
            $form->setValues(Mage::registry('slider_data')->getData());
        }
        return parent::_prepareForm();
    }
}
