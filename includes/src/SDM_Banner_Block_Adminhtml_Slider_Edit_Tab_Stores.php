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
 * SDM_Banner_Block_Adminhtml_Slider_Edit_Tab_Stores class
 */
class SDM_Banner_Block_Adminhtml_Slider_Edit_Tab_Stores
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form fields
     *
     * @return SDM_Banner_Block_Adminhtml_Slider_Edit_Tab_Stores
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('slider_form',
            array('legend'=>Mage::helper('slider')->__("Websites"))
        );

        $websiteOptions = Mage::getSingleton('adminhtml/system_store')
            ->getWebsiteValuesForForm(false, true);
        unset($websiteOptions[0]);  // Remove Admin store
        $fieldset->addField('stores', 'multiselect', array(
            'label'     => Mage::helper('slider')->__('Visible in'),
            'required'  => true,
            'name'      => 'stores[]',
            'values'    => $websiteOptions
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
