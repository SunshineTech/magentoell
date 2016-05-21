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
 * SDM_Compatibility_Block_Adminhtml_Compatibility_Edit_Form class
 */
class SDM_Compatibility_Block_Adminhtml_Compatibility_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form
     *
     * @return SDM_Compatibility_Block_Adminhtml_Compatibility_Edit_Form
     */
    protected function _prepareForm()
    {
        $compatibility = Mage::registry('compatibility');
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
            array('legend' => $this->__('Compatiblity'))
        );

        // Hidden input for record ID
        if ($compatibility->getId()) {
            $fieldset->addField(
                'id',
                'hidden',
                array('name' => 'id')
            );
        }

        $fieldset->addField(
            'die_productline_id',
            'select',
            array(
                'name' => 'die_productline_id',
                'label' => $this->__('Die Product Line'),
                'required' => true,
                'values' => Mage::getSingleton('compatibility/source_productline')->getAllNameOptions(),
            )
        );
        $fieldset->addField(
            'machine_productline_id',
            'select',
            array(
                'name' => 'machine_productline_id',
                'label' => $this->__('Machine Product Line'),
                'required' => true,
                'values' => Mage::getSingleton('compatibility/source_productline')->getAllNameOptions(),
            )
        );
        $fieldset->addField(
            'associated_products',
            'text',
            array(
                'name' => 'associated_products',
                'label' => $this->__('Associated Products'),
                'required' => true,
                'note' => 'This must be comma-delimited'
            )
        );
        $fieldset->addField(
            'position',
            'text',
            array(
                'name' => 'position',
                'label' => $this->__('Position'),
                'required' => false,
                'note' => $this->__('Default: 0'),

            )
        );

        $form->setValues($compatibility->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
