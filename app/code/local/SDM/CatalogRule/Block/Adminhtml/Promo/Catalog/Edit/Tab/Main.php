<?php
/**
 * Separation Degrees One
 *
 * Magento catalog rule customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CatalogRule
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_CatalogRule_Block_Adminhtml_Promo_Catalog_Edit_Tab_Main class
 */
class SDM_CatalogRule_Block_Adminhtml_Promo_Catalog_Edit_Tab_Main
    extends Mage_Adminhtml_Block_Promo_Catalog_Edit_Tab_Main
{
    /**
     * Prepare form fields
     *
     * @return SDM_CatalogRule_Block_Adminhtml_Promo_Catalog_Edit_Tab_Main
     */
    protected function _prepareForm()
    {
        $return = parent::_prepareForm();

        $model = Mage::registry('current_promo_catalog_rule');

        $baseFieldset = $this->getForm()->getElement('base_fieldset');
        $baseFieldset->addField('hide_sale_icon', 'select', array(
            'name'      => 'hide_sale_icon',
            'label'     => Mage::helper('sdm_catalogrule')->__('Hide Sale Icon'),
            'title'     => Mage::helper('sdm_catalogrule')->__('Hide Sale Icon'),
            'required'  => false,
            'value'     => $model->getData('hide_sale_icon'),
            'values'    => array( '0' => 'No', '1' => 'Yes' )
        ));
        $baseFieldset->addField('custom_sale_icon', 'image', array(
            'name'      => 'custom_sale_icon',
            'label'     => Mage::helper('sdm_catalogrule')->__('Custom Sale Icon'),
            'title'     => Mage::helper('sdm_catalogrule')->__('Custom Sale Icon'),
            'required'  => false,
            'note'      => 'Default sale icons will be used if no custom icon is uploaded',
            'value'     => $model->getData('custom_sale_icon')
        ));

        $this->getForm()->setValues($model->getData());

        return $return;
    }
}
