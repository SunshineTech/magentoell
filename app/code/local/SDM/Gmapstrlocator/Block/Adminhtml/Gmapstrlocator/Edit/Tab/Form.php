<?php
/**
 * Separation Degrees Media
 *
 * This module handles all the store locator functionality for Ellison.
 *
 * The original code from this module is based off the FME_Gmapstrlocator module. We converted their
 * module to an SDM module rather than extending from it because the amount of modifications and
 * rewrites necessary for it to fit Ellison's spec were extensive, yet we still felt there was value
 * in using FME's module as a starting point.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Gmapstrlocator
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator_Edit_Tab_Form class
 */
class SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator_Edit_Tab_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form
     *
     * @return SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator_Edit_Tab_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        /**
         * Store Location
         * @var [type]
         */
        $storeLocation = $form->addFieldset(
            'gmapstrlocator_form_location',
            array('legend' => Mage::helper('gmapstrlocator')->__('Store Location'))
        );

        $storeLocation->addField('store_name', 'text', array(
            'label'     => Mage::helper('gmapstrlocator')->__('Store Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'store_name'
        ));

        $storeLocation->addField('store_number', 'text', array(
            'label'     => Mage::helper('gmapstrlocator')->__('Store Number'),
            'required'  => false,
            'name'      => 'store_number'
        ));

        $storeLocation->addField('store_type', 'multiselect', array(
            'name'  => 'store_type',
            'label'     => 'Store Type',
            'values'    => Mage::getModel('gmapstrlocator/system_config_source_storetypes')->toMultiSelectArray(),
            'class'     => 'required-entry',
            'required'  => true,
            'style'     => 'height: 10em;'
        ));

        $storeLocation->addField('image', 'image', array(
            'label'    => $this->helper('gmapstrlocator')->__('Image'),
            'name'     => 'image',
        ));

        $storeLocation->addField('address', 'text', array(
            'label'     => Mage::helper('gmapstrlocator')->__('Address'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'address'
        ));

        $storeLocation->addField('address2', 'text', array(
            'label'     => Mage::helper('gmapstrlocator')->__('Address 2'),
            'required'  => false,
            'name'      => 'address2'
        ));

        $storeLocation->addField('city', 'text', array(
            'label'     => Mage::helper('gmapstrlocator')->__('City'),
            'name'      => 'city'
        ));

        $storeLocation->addField('state', 'text', array(
            'label'     => Mage::helper('gmapstrlocator')->__('State'),
            'class'     => '',
            'required'  => false,
            'name'      => 'state',
            'note'      => 'If US state, use two digit state code (eg. CA, NV, CO...)'
        ));

        $storeLocation->addField('country', 'select', array(
            'name'  => 'country',
            'label'     => 'Country',
            'values'    => Mage::getModel('gmapstrlocator/system_config_source_countrylist')->toOptionArray(),
            'class'     => 'required-entry',
            'required'  => true,
            'after_element_html' => "<script>jQuery(function(){if (jQuery('#country').val() == '') jQuery('#country').val('United States')});</script>"
        ));

        $storeLocation->addField('postal_code', 'text', array(
            'label'     => Mage::helper('gmapstrlocator')->__('Postal / Zip Code'),
            'name'      => 'postal_code',
            'style'     => 'width:150px;'
        ));

        $storeLocation->addField('latitude', 'text', array(
            'label'     => Mage::helper('gmapstrlocator')->__('Latitude'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'latitude',
            'style'     => 'width:150px;'
        ));

        $storeLocation->addField('longitude', 'text', array(
            'label'     => Mage::helper('gmapstrlocator')->__('Longitude'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'longitude',
            'style'     => 'width:150px;',
            'after_element_html' => "<br><a href='#' class='recalc-lat-lon' data-key="
                . Mage::helper('gmapstrlocator')->getGMapAPIKey() . ">Calculate Lat/Lon</a>"
        ));

        $storeLocation->addField('store_phone', 'text', array(
            'label'     => Mage::helper('gmapstrlocator')->__('Phone'),
            'class'     => '',
            'required'  => false,
            'name'      => 'store_phone'
        ));

        $storeLocation->addField('store_fax', 'text', array(
            'label'     => Mage::helper('gmapstrlocator')->__('Fax'),
            'class'     => '',
            'required'  => false,
            'name'      => 'store_fax'
        ));

        $storeLocation->addField('store_email', 'text', array(
            'label'     => Mage::helper('gmapstrlocator')->__('Email'),
            'class'     => '',
            'required'  => false,
            'name'      => 'store_email'
        ));

        $storeLocation->addField('store_website', 'text', array(
            'label'     => Mage::helper('gmapstrlocator')->__('Website'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'store_website'
        ));

        /**
         * Store information
         */
        $storeInformation = $form->addFieldset(
            'gmapstrlocator_form',
            array('legend' => Mage::helper('gmapstrlocator')->__('Store information'))
        );

        $storeInformation->addField('has_design_center', 'select', array(
            'name'  => 'has_design_center',
            'label'     => 'Has Design Center?',
            'values'    => Mage::getModel('gmapstrlocator/system_config_source_yesno')->toOptionArray(),
            'class'     => 'required-entry',
            'required'  => true
        ));

        $storeInformation->addField('product_lines', 'multiselect', array(
            'name'      => 'product_lines[]',
            'label'     => Mage::helper('gmapstrlocator')->__('Product Lines'),
            'title'     => Mage::helper('gmapstrlocator')->__('Product Lines'),
            'required'  => true,
            'values'    => Mage::getModel('gmapstrlocator/system_config_source_productlines')->toMultiSelectArray(),
            'style'     => 'height: 130px'
        ));

        $storeInformation->addField('agent_type', 'select', array(
            'name'  => 'agent_type',
            'label'     => 'Agent Types',
            'values'    => Mage::getModel('gmapstrlocator/system_config_source_agenttypes')->toOptionArray(),
            'class'     => 'required-entry',
            'required'  => true
        ));

        $storeInformation->addField('representative_serving', 'text', array(
            'name'  => 'representative_serving',
            'label'     => 'Representative Serving',
            'required'  => false,
            'note'      => "Enter state codes as comma separated values (eg. CA, NV, CO...)"
        ));

        $storeInformation->addField('status', 'select', array(
            'label'     => Mage::helper('gmapstrlocator')->__('Status'),
            'name'      => 'status',
            'values'    => array(
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('gmapstrlocator')->__('Enabled'),
                ),
                array(
                    'value'     => 2,
                    'label'     => Mage::helper('gmapstrlocator')->__('Disabled'),
                ),
            )
        ));

        $storeInformation->addField('website_id', 'multiselect', array(
            'name'      => 'website_id[]',
            'label'     => Mage::helper('gmapstrlocator')->__('Website'),
            'title'     => Mage::helper('gmapstrlocator')->__('Website'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm(false, false)
        ));

        /**
         * Internal comments
         */
        $storeInformation = $form->addFieldset(
            'gmapstrlocator_form_comments',
            array('legend' => Mage::helper('gmapstrlocator')->__('Internal Comments'))
        );

        $storeInformation->addField('internal_comments', 'textarea', array(
            'name'      => 'internal_comments',
            'label'     => Mage::helper('gmapstrlocator')->__('Internal Comments'),
            'title'     => Mage::helper('gmapstrlocator')->__('Internal Comments'),
            'style'     => 'width:400px; height:200px;',
            'wysiwyg'   => true,
            'required'  => false
        ));

        if (Mage::getSingleton('adminhtml/session')->getGmapstrlocatorData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getGmapstrlocatorData());
            Mage::getSingleton('adminhtml/session')->setGmapstrlocatorData(null);
        } elseif (Mage::registry('gmapstrlocator_data')) {
            $form->setValues(Mage::registry('gmapstrlocator_data')->getData());
        }

        return parent::_prepareForm();
    }
}
