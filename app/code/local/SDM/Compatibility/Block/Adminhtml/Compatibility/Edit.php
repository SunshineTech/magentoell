<?php
/**
 * Separation Degrees Media
 *
 * Implements the product compatibility functionality.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Compatibility
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Compatibility_Block_Adminhtml_Compatibility_Edit class
 */
class SDM_Compatibility_Block_Adminhtml_Compatibility_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Initialize
     */
    public function __construct()
    {
        $this->_blockGroup = 'compatibility';
        $this->_controller = 'adminhtml_compatibility';

        parent::__construct();

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
        if (Mage::registry('compatibility')->getId()) {
            return $this->__('Edit Compatibility');
        } else {
            return $this->__('New Compatibility');
        }
    }
}
