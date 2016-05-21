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
 * Edit playlist tabs
 */
class SDM_YoutubeFeed_Block_Adminhtml_Playlist_Edit_Tab_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form fields
     *
     * @return SDM_YoutubeFeed_Block_Adminhtml_Playlist_Edit_Tab_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'sdm_youtubefeed_playlist_form',
            array(
                'legend' => Mage::helper('sdm_youtubefeed')->__('Playlist information')
            )
        );
        $fieldset->addField('identifier', 'text', array(
            'label'    => Mage::helper('sdm_youtubefeed')->__('Identifier'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'identifier',
        ));
        $fieldset->addField('name', 'text', array(
            'label'    => Mage::helper('sdm_youtubefeed')->__('Name'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'name',
        ));
        $options = Mage::getSingleton('sdm_youtubefeed/adminhtml_system_config_source_channel')->toOptionArray();
        $channels = array();
        foreach ($options as $option) {
            $channels[$option['value']] = $option['label'];
        }
        $fieldset->addField('channel_id', 'select', array(
            'label'    => Mage::helper('sdm_youtubefeed')->__('Channel'),
            'class'    => 'required-entry',
            'required' => true,
            'options'  => $channels,
            'name'     => 'channel_id',
        ));
        $fieldset->addField('playlist_status', 'select', array(
            'label'    => Mage::helper('sdm_youtubefeed')->__('Status'),
            'class'    => 'required-entry',
            'required' => true,
            'options'  => array(
                SDM_YoutubeFeed_Model_Playlist::STATUS_ENABLED  => Mage::helper('sdm_youtubefeed')->__('Enabled'),
                SDM_YoutubeFeed_Model_Playlist::STATUS_DISABLED => Mage::helper('sdm_youtubefeed')->__('Disabled')
            ),
            'name'     => 'playlist_status',
        ));
        $fieldset->addField('position', 'text', array(
            'label' => Mage::helper('sdm_youtubefeed')->__('Position'),
            'name'  => 'position',
        ));
        if (Mage::getSingleton('adminhtml/session')->getPlaylistData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getPlaylistData());
            Mage::getSingleton('adminhtml/session')->setPlaylistData(null);
        } elseif (Mage::registry('playlist_data')) {
            $form->setValues(Mage::registry('playlist_data')->getData());
        }
        return parent::_prepareForm();
    }
}
