<?php
/**
 * Separation Degrees One
 *
 * Implements the customer/retailer discount logic for viewing and obtaining
 * discounts and prices, as wel as managing the customer groups.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CustomerDiscount
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_CustomerDiscount_Block_Adminhtml_Discountgroup_Edit_Form class
 */
class SDM_CustomerDiscount_Block_Adminhtml_Discountgroup_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form
     *
     * @return SDM_CustomerDiscount_Block_Adminhtml_Discountgroup_Edit_Form
     */
    protected function _prepareForm()
    {
        $discountGroup = Mage::registry('discount_group');

        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl(
                    '*/*/save',
                    array('id' => $this->getRequest()->getParam('id'))
                ),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ));

        $fieldset = $form->addFieldset(
            'general',
            array('legend' => $this->__('Customer Discount'))
        );

        // Hidden input for record ID
        if ($discountGroup->getId()) {
            $fieldset->addField(
                'id',
                'hidden',
                array(
                    'name' => 'id',
                    // 'value' => $discountGroup->getId()
                )
            );
        }

        $fieldset->addField(
            'customer_group_id',
            'select',
            array(
                'name' => 'customer_group_id',
                'label' => $this->__('Customer Group'),
                'required' => true,
                'values' => Mage::helper('customerdiscount')->getAllCustomerGroupOptions(),
            )
        );
        $fieldset->addField(
            'category_id',
            'select',
            array(
                'name' => 'category_id',
                'label' => $this->__('Discount Category'),
                'required' => true,
                'values' => Mage::helper('customerdiscount')->getAllDiscountCategoryOptions(),
            )
        );

        $fieldset->addField(
            'amount',
            'text',
            array(
                'name' => 'amount',
                'label' => $this->__('Discount Amount (%)'),
                'required' => true,
            )
        );

        $form->setValues($discountGroup->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
