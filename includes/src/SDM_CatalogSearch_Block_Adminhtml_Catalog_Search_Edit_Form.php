<?php
/**
 * Separation Degrees One
 *
 * Magento catalog search customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CatalogSearch
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_CatalogSearch_Block_Adminhtml_Catalog_Search_Edit_Form class
 */
class SDM_CatalogSearch_Block_Adminhtml_Catalog_Search_Edit_Form extends Mage_Adminhtml_Block_Catalog_Search_Edit_Form
{
    /**
     * Prepare form fields
     *
     * @return Mage_Adminhtml_Block_Catalog_Search_Edit_Form
     */
    protected function _prepareForm()
    {
        $return = parent::_prepareForm();

        $this->getForm('edit_form')
            ->getElement('base_fieldset')
            ->addField('type', 'text', array(
                'name'  => 'type',
                'label' => Mage::helper('catalog')->__('Type'),
                'title' => Mage::helper('catalog')->__('Type'),
                'class' => 'type',
                'note'  => Mage::helper('catalog')->__('Was this a product or project search?'),
                'value' => Mage::registry('current_catalog_search')->getType()
            ));

        return $return;
    }
}
