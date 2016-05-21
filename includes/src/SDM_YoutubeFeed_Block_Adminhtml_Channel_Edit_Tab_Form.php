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
 * Edit channel tabs
 */
class SDM_YoutubeFeed_Block_Adminhtml_Channel_Edit_Tab_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form fields
     *
     * @return SDM_YoutubeFeed_Block_Adminhtml_Channel_Edit_Tab_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'sdm_youtubefeed_channel_form',
            array(
                'legend' => Mage::helper('sdm_youtubefeed')->__('Channel information')
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
        $fieldset->addField('position', 'text', array(
            'label' => Mage::helper('sdm_youtubefeed')->__('Position'),
            'name'  => 'position',
        ));
        if (Mage::getSingleton('adminhtml/session')->getChannelData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getChannelData());
            Mage::getSingleton('adminhtml/session')->setChannelData(null);
        } elseif (Mage::registry('channel_data')) {
            $form->setValues(Mage::registry('channel_data')->getData());
        }
        return parent::_prepareForm();
    }
}
