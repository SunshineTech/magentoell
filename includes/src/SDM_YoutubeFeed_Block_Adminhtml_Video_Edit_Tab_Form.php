<?php
/**
 * Separation Degrees Media
 *
 * Embed Youtube Videos and Playlists
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_YoutubeFeed
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * Edit video tabs
 */
class SDM_YoutubeFeed_Block_Adminhtml_Video_Edit_Tab_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form fields
     *
     * @return SDM_YoutubeFeed_Block_Adminhtml_Video_Edit_Tab_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'sdm_youtubefeed_video_form',
            array(
                'legend' => Mage::helper('sdm_youtubefeed')->__('Video information')
            )
        );
        $fieldset->addField('identifier', 'label', array(
            'label' => Mage::helper('sdm_youtubefeed')->__('Identifier'),
            'name'  => 'identifier',
        ));
        $fieldset->addField('name', 'text', array(
            'label'    => Mage::helper('sdm_youtubefeed')->__('Name'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'name',
        ));
        $fieldset->addField('description', 'textarea', array(
            'label' => Mage::helper('sdm_youtubefeed')->__('Description'),
            'name'  => 'description',
        ));
        $fieldset->addField('status', 'select', array(
            'label'    => Mage::helper('sdm_youtubefeed')->__('Status'),
            'class'    => 'required-entry',
            'required' => true,
            'options'  => array(
                SDM_YoutubeFeed_Model_Video::STATUS_ENABLED  => Mage::helper('sdm_youtubefeed')->__('Enabled'),
                SDM_YoutubeFeed_Model_Video::STATUS_DISABLED => Mage::helper('sdm_youtubefeed')->__('Disabled')
            ),
            'name'     => 'status',
        ));
        $fieldset->addField('featured', 'select', array(
            'label'    => Mage::helper('sdm_youtubefeed')->__('Featured'),
            'class'    => 'required-entry',
            'required' => true,
            'options'  => array(
                SDM_YoutubeFeed_Model_Video::FEATURED_YES  => Mage::helper('sdm_youtubefeed')->__('Yes'),
                SDM_YoutubeFeed_Model_Video::FEATURED_NO => Mage::helper('sdm_youtubefeed')->__('No')
            ),
            'name'     => 'featured',
        ));
        $fieldset->addField('designer', 'multiselect', array(
            'label'  => Mage::helper('sdm_youtubefeed')->__('Designers'),
            'values' => Mage::getSingleton('sdm_designer/adminhtml_system_config_source_designer')
                ->toOptionArray(),
            'name'   => 'designer',
        ));
        $fieldset->addField('file_url', 'text', array(
            'label' => Mage::helper('sdm_youtubefeed')->__('PDF URL'),
            'name'  => 'file_url',
            'note'  => 'File location: "/media/uploads/pdfs/instructions/<filename>.pdf"'
        ));
        if (Mage::getSingleton('adminhtml/session')->getVideoData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getVideoData());
            Mage::getSingleton('adminhtml/session')->setVideoData(null);
        } elseif (Mage::registry('video_data')) {
            $form->setValues(Mage::registry('video_data')->getData());
        }
        return parent::_prepareForm();
    }
}
