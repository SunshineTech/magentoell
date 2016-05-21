<?php
/**
 * Separation Degrees One
 *
 * Banner Ads
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Banner
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */
/**
 * SDM_Banner_Block_Adminhtml_Slider_Edit_Tab_Stores class
 */

class SDM_YoutubeFeed_Block_Adminhtml_Playlist_Edit_Tab_Websites
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form fields
     *
     * @return SDM_Banner_Block_Adminhtml_Slider_Edit_Tab_Stores
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('sdm_youtubefeed_playlist_form',
            array('legend'=>Mage::helper('sdm_youtubefeed')->__("Websites"))
        );

        $websiteOptions = Mage::getSingleton('adminhtml/system_store')
            ->getWebsiteValuesForForm(false, true);
        unset($websiteOptions[0]);  // Remove Admin store
        $fieldset->addField('websites', 'multiselect', array(
            'label'     => Mage::helper('sdm_youtubefeed')->__('Visible in'),
            'required'  => true,
            'name'      => 'websites[]',
            'values'    => $websiteOptions
        ));
        if (Mage::getSingleton('adminhtml/session')->getYoutubePlaylistData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getYoutubePlaylistData());
            Mage::getSingleton('adminhtml/session')->setYoutubePlaylistData(null);
        } elseif (Mage::registry('youtube_playlist_data')) {
            $form->setValues(Mage::registry('youtube_playlist_data')->getData());
        }
        return parent::_prepareForm();
    }
}
