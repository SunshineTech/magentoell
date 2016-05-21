<?php
/**
 * Separation Degrees Media
 *
 * Ellison's custom product taxonomy implementation.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Taxonomy
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Taxonomy_Block_Adminhtml_Item_Edit class
 */
class SDM_Taxonomy_Block_Adminhtml_Item_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Initialize
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'sdm_taxonomy_adminhtml';
        $this->_controller = 'item';
        $this->_mode = 'edit';

        $newOrEdit = $this->getRequest()->getParam('id')
            ? $this->__('Edit')
            : $this->__('New');
        $this->_headerText =  $newOrEdit . ' ' . $this->__('Taxonomy Item');

        $this->_addButton(
            'save_and_continue',
            array(
                'label' => Mage::helper('taxonomy')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinue()',
                'class' => 'save'
            ),
            -100
        );

        $this->_formScripts[] = "function saveAndContinue(){
            editForm.submit($('edit_form').action + 'back/edit/');
        }";
    }
}
