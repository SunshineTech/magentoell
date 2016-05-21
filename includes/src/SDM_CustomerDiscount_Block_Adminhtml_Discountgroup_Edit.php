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
 * SDM_CustomerDiscount_Block_Adminhtml_Discountgroup_Edit class
 */
class SDM_CustomerDiscount_Block_Adminhtml_Discountgroup_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Initialize
     */
    public function __construct()
    {
        $this->_blockGroup = 'customerdiscount';
        $this->_controller = 'adminhtml_discountgroup';

        parent::__construct();

        $this->_updateButton(
            'delete',
            'onclick',
            'deleteConfirm(\''. Mage::helper('adminhtml')->__('Are you sure you want to delete?')
                    .'\', \'' . $this->getDeleteUrl() . '\')'
        );

        $this->_addButton(
            'save_and_continue',
            array(
                'label' => $this->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinue()',
                'class' => 'save'
            ),
            -100
        );
        $this->_formScripts[] = "function saveAndContinue(){
            editForm.submit($('edit_form').action + 'back/edit/');
        }";
    }

    /**
     * Get Header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('discount_group')->getId()) {
            return $this->__('Edit Discount Group');
        } else {
            return $this->__('New Discount Group');
        }
    }

    /**
     * Get delete URL
     *
     * @return str
     */
    public function getProductLineDeleteUrl()
    {
        return $this->getUrl(
            '*/*/delete',
            array($this->_objectId => $this->getRequest()->getParam($this->_objectId))
        );
    }
}
