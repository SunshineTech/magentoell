<?php
/**
 * Separation Degrees One
 *
 * Ellison's Mage_Customer customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Customer
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Customer_Block_Adminhtml_Customer_Group_Edit_Form class
 */
class SDM_Customer_Block_Adminhtml_Customer_Group_Edit_Form
    extends Mage_Adminhtml_Block_Customer_Group_Edit_Form
{
    /**
     * Prepare form for render and add custom fields
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $customerGroup = Mage::registry('current_group');

        // Get previously constructed form
        $form = $this->getForm();
        $fieldset = $form->getElement('base_fieldset');

        $fieldset->addField(
            'position',
            'text',
            array(
                'name'  => 'position',
                'label' => Mage::helper('sdm_customer')->__('Position'),
                'title' => Mage::helper('sdm_customer')->__('Position'),
                'class' => 'required-entry',
                'note' => 'Default: 0',
                'value' => '0', // Default value
                'required' => true,
            )
        );
        $fieldset->addField(
            'min_qty_override',
            'select',
            array(
                'name'  => 'min_qty_override',
                'label' => Mage::helper('sdm_customer')->__('Min. Qty. Override'),
                'title' => Mage::helper('sdm_customer')->__('Min. Qty. Override'),
                'class' => 'required-entry',
                'values' => array('0' => 'No', '1' => 'Yes'),
                // 'note' => 'Default: 0 (1 to override)',
                'value' => '0', // Default value
                'required' => true,
            )
        );

        if (Mage::getSingleton('adminhtml/session')->getCustomerGroupData()) {
            $form->addValues(Mage::getSingleton('adminhtml/session')->getCustomerGroupData());
            Mage::getSingleton('adminhtml/session')->setCustomerGroupData(null);
        } else {
            $form->addValues($customerGroup->getData());
        }

        $this->setForm($form);
    }
}
