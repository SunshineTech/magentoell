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
 * SDM_Compatibility_Block_Adminhtml_Productline_Edit_Form class
 */
class SDM_Compatibility_Block_Adminhtml_Productline_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form
     *
     * @return SDM_Compatibility_Block_Adminhtml_Productline_Edit_Form
     */
    protected function _prepareForm()
    {
        $productLine = Mage::registry('productline');
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl(
                    '*/*/psave',
                    array('id' => $this->getRequest()->getParam('id'))
                ),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ));

        $fieldset = $form->addFieldset(
            'general',
            array('legend' => $this->__('Product Line'))
        );

        // Hidden input for record ID
        if ($productLine->getId()) {
            $fieldset->addField(
                'productline_id',
                'hidden',
                array('name' => 'productline_id')
            );
        }

        $fieldset->addField(
            'name',
            'text',
            array(
                'name' => 'name',
                'label' => $this->__('Product Line'),
                'required' => true,
            )
        );
        if ($productLine->getId()) {
            $fieldset->addField(
                'code',
                'text',
                array(
                    'name' => 'code',
                    'label' => $this->__('Product Code'),
                    'note' => $this->__('This field is read-only'),
                    'readonly' => true
                )
            );
        }
        $websiteOptions = Mage::getSingleton('adminhtml/system_store')
            ->getWebsiteValuesForForm(false, true);
        unset($websiteOptions[0]);  // Remove Admin store
        $fieldset->addField(
            'website_ids',
            'multiselect',
            array(
                'name' => 'website_ids[]',
                'label' => $this->__('Sites Enabled'),
                'note' => $this->__('Select sites for which to enable this product line.'),
                'required' => true,
                'values' => $websiteOptions,
            )
        );
        $fieldset->addField(
            'type',
            'select',
            array(
                'name' => 'type',
                'label' => $this->__('Product Type'),
                'required' => true,
                'values' => Mage::helper('compatibility')->getProductTypeArray()

            )
        );
        $fieldset->addField(
            'image_link',
            'image',
            array(
                'name'      => 'image_link',
                'label'     => $this->__('Image File'),
                'required'  => true,
                'note' => 'Compataibility image should be 500x500'
            )
        );
        $fieldset->addField(
            'image_page_link',
            'text',
            array(
                'name'      => 'image_page_link',
                'label'     => $this->__('Image Page Link'),
                'required'  => false,
                'note' => $this->__('Do not include the domain'),
            )
        );
        $fieldset->addField(
            'description',
            'textarea',
            array(
                'name' => 'description',
                'label' => $this->__('Description'),
                'required' => false,
            )
        );
        $fieldset->addField(
            'rich_description',
            'editor',
            array(
                'name' => 'rich_description',
                'label' => $this->__('Rich Description'),
                'wysiwyg' => true,
                'required' => false,
                'style' => 'width: 95%;height: 24em;',
                'state' => 'html',
                'config'    => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
                'after_element_html' =>  '<p class="note"><span>Illustration images should be 800x800</span></p>'
            )
        );

        $form->setValues($productLine->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
