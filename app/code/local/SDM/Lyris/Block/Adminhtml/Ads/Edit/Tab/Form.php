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
class SDM_Lyris_Block_Adminhtml_Ads_Edit_Tab_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form fields
     * @return SDM_Lyris_Block_Adminhtml_Ads_Edit_Tab_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('sdm_lyris_ads_form',
            array(
                'legend' => Mage::helper('sdm_lyris')->__('Thumbnail Information')
            )
        );
        $fieldset->addField('title', 'text', array(
            'label'     => Mage::helper('sdm_lyris')->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'title',
            'tabindex' => 1
            ));
        $afterElementHtml = '<p class="nm" style="color:green;" ><small>' .
        '( Min Image Size should be 1000 in px. )' . '</small></p>';
        $fieldset->addField('image', 'image', array(
            'label'     => Mage::helper('sdm_lyris')->__('Image'),
            'required'  => true,
            'name'      => 'image',
            'after_element_html'=>$afterElementHtml,
            'tabindex' => 1
            ));

        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('sdm_lyris')->__('Status'),
            'name'      => 'status',
            'values'    => array(
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('sdm_lyris')->__('Enabled'),
                    ),
                array(
                    'value'     => 2,
                    'label'     => Mage::helper('sdm_lyris')->__('Disabled'),
                    ),
                ),
            'tabindex' => 1
            ));

        if (Mage::getSingleton('adminhtml/session')->getAdsData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getAdsData());
            Mage::getSingleton('adminhtml/session')->setSliderData(null);
        } elseif (Mage::registry('ads_data')) {
            $form->setValues(Mage::registry('ads_data')->getData());
        }
        return parent::_prepareForm();
    }
}
