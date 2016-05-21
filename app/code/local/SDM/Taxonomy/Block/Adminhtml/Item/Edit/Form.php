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
 * SDM_Taxonomy_Block_Adminhtml_Item_Edit_Form class
 */
class SDM_Taxonomy_Block_Adminhtml_Item_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form
     *
     * @return SDM_Taxonomy_Block_Adminhtml_Item_Edit_Form
     */
    protected function _prepareForm()
    {
        $item = Mage::registry('current_item');
        $assignedProducts = Mage::registry('assigned_products');

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl(
                'adminhtml/taxonomy_item/save',
                array(
                    '_current' => true,
                    'continue' => 0,
                )
            ),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));
        $form->setUseContainer(true);
        $this->setForm($form);

        $websiteOptions = Mage::getSingleton('adminhtml/system_store')
            ->getWebsiteValuesForForm(false, true);
        unset($websiteOptions[0]);  // Remove Admin store
        $websiteData = $this->_convertWebsiteOptions($websiteOptions);

        $fieldset = $form->addFieldset(
            'general',
            array(
                'legend' => $this->__('Taxonomy Item Details')
            )
        );

        $config = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
        $config->setData(
            'files_browser_window_url',
            Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index/')
        );
        $config->setData(
            'directives_url',
            Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive')
        );
        $config->setData(
            'directives_url_quoted',
            preg_quote($config->getData('directives_url'))
        );
        $config->setData(
            'widget_window_url',
            Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/widget/index')
        );
        // Add the fields that we want to be editable.
        $this->_addFieldsToFieldset($fieldset, array(
            'name' => array(
                'label' => $this->__('Name'),
                'input' => 'text',
                'required' => true,
            ),
            // Code is auto-generated
            'code' => array(
                'label' => $this->__('Code'),
                'input' => 'text',
                'note' => 'If left empty, it will be auto-generated',
                // 'readonly' => true,
            ),
            'type' => array(
                'label' => $this->__('Type'),
                'input' => 'select',
                'required' => true,
                'options' => Mage::helper('taxonomy')->getTypes(),
                'note' => $this->__(
                    'For "Special", it may be wise to save once before adding '
                        . 'products in case admin session expires.'
                ),
            ),
            'image_url' => array(
                'label' => $this->__('Image'),
                'input' => 'image',
                'required' => false,
                'after_element_html' =>  '<p class="note" style="width: 350px;"><span>Used for catalog view and hook pages.<br>For image with text, recommended image size: 400px by 400px<br>Stand-alone image, recommended minimum width: 740px</span></p>'
            ),
            'swatch' => array(
                'label' => $this->__('Swatch'),
                'input' => 'image',
                'required' => false,
                'after_element_html' =>  '<p class="note"><span>Used for product view pages.<br>Recommended image size: 275px by 275px</span></p>'
            ),
            'description' => array(
                'label' => $this->__('Description'),
                'input' => 'editor',
                'wysiwyg' => true,
                'required' => false,
                'style' => 'width: 95%;height: 12em;',
                'state' => 'html',
                'config'    => $config,
                'after_element_html' =>  '<p class="note"><span>Used for catalog view and hook pages</span></p>'
            ),
            'rich_description' => array(
                'label' => $this->__('Rich Description'),
                'input' => 'editor',
                'wysiwyg' => true,
                'required' => false,
                'style' => 'width: 95%;height: 24em;',
                'state' => 'html',
                'config'    => $config,
                'after_element_html' =>  '<p class="note"><span>Used for designer detail page</span></p>'
            ),
            'position' => array(
                'label' => $this->__('Position'),
                'input' => 'text',
                'required' => false,
            ),
            'website_ids' => array(
                'input' => 'multiselect',
                'name' => 'website_ids[]',
                'class' => 'website-multiselect-box',
                'required' => true,
                'label' => $this->__('Sites Enabled'),
                'note' => $this->__('Select sites for which to enable this product line.'),
                'required' => true,
                'values' => $websiteOptions,
            ),
        ));

        /**
         * Add website fields to the form. 'start_date_X' and 'end_date_X' must
         * match what the product data contains.
         *
         * @see SDM_Taxonomy_Model_Item::_afterLoad()
         * @see Mage_Adminhtml_Block_Promo_Quote_Edit_Tab_Main::_prepareForm()
         *      for setting up calendar
         */
        foreach ($websiteData as $id => $website) {
            $this->_addFieldsToFieldset(
                $fieldset,
                array(
                    "start_date_$id" => array(
                        'input'        => 'date',
                        'label'        => $this->__('Start Date (%s)', $website['label']),
                        'required'     => false,
                        'image'        => $this->getSkinUrl('images/grid-cal.gif'),
                        'format'       => Varien_Date::DATE_INTERNAL_FORMAT,
                        'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
                    ),
                    "end_date_$id" => array(
                        'input'        => 'date',
                        'label'        => $this->__('End Date (%s)', $website['label']),
                        'required'     => false,
                        'image'        => $this->getSkinUrl('images/grid-cal.gif'),
                        'format'       => Varien_Date::DATE_INTERNAL_FORMAT,
                        'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
                    )
                )
            );
        }

        // Hidden field to be used as a reference point for the Special product grid
        $fieldset->addField(
        'reference-1',
        'text',
        array('style' => 'display: none')
        );

        // Hidden input for record ID
        if ($item->getId()) {
            $fieldset->addField(
            'entity_id',
            'hidden',
             array('name' => 'taxonomyData[entity_id]', 'value' => $item->getId())
            );
        }

        if (!empty($assignedProducts)) {
            foreach ($assignedProducts as $i => $productId) {
                $fieldset->addField(
                'product_id_' . $i,
                'hidden',
                array('name' => 'previously_assigned_products[]', 'value' => $productId)
                );
            }
        }

        return $this;
    }

    /**
     * Warpper to standardize and data to add to fieldset
     *
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param array                             $fields
     *
     * @return SDM_Taxonomy_Block_Adminhtml_Item_Edit_Form
     */
    protected function _addFieldsToFieldset(Varien_Data_Form_Element_Fieldset $fieldset, $fields)
    {
        $requestData = new Varien_Object($this->getRequest()
            ->getPost('taxonomyData'));

        foreach ($fields as $name => $data) {
            if ($requestValue = $requestData->getData($name)) {
                $data['value'] = $requestValue;
            }

            // Wrap all fields with taxonomyData group.
            $data['name'] = "taxonomyData[$name]";

            // Generally, label and title are always the same.
            $data['title'] = $data['label'];

            // If no new value exists, use the existing data.
            if (!array_key_exists('value', $data)) {
                $data['value'] = $this->_getItem()->getData($name);
            }

            // Finally, call vanilla functionality to add field.
            $fieldset->addField($name, $data['input'], $data);
        }

        return $this;
    }

    /**
     * Get the objet in Mage registry and set it to the form. Some of the data
     * requires additional work to be set to the form.
     *
     * @return SDM_Taxonomy_Model_Item
     */
    protected function _getItem()
    {
        if (!$this->hasData('current_item')) {
            // This will have been set in the controller
            $item = Mage::registry('current_item');

            // Just in case the controller does not register the item
            if (!$item instanceof SDM_Taxonomy_Model_Item) {
                $item = Mage::getModel('taxonomy/item');
            }

            $this->setData('current_item', $item);
        }
        // Mage::log($item->getData());

        return $this->getData('current_item');
    }

    /**
     * Convert website data array to an associative array where the key is the
     * website ID and the value is an array of website ID and code.
     *
     * @param array $websites
     *
     * @return array
     */
    protected function _convertWebsiteOptions($websites)
    {
        $map = array();

        foreach ($websites as $website) {
            $map[$website['value']]['label'] = $website['label'];
            $map[$website['value']]['code'] = Mage::helper('sdm_core')
                ->transformNameToCode($website['label']);
        }
        return $map;
    }
}
