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
class SDM_YoutubeFeed_Block_Adminhtml_Playlist_Edit_Tabs
    extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Setup tabs
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('playlist_tabs')
            ->setDestElementId('edit_form')
            ->setTitle(Mage::helper('sdm_youtubefeed')->__('Playlist Information'));
    }

    /**
     * Create tab
     *
     * @return SDM_YoutubeFeed_Block_Adminhtml_Playlist_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'   => Mage::helper('sdm_youtubefeed')->__('Playlist Information'),
            'title'   => Mage::helper('sdm_youtubefeed')->__('Playlist Information'),
            'content' => $this->getLayout()->createBlock('sdm_youtubefeed/adminhtml_playlist_edit_tab_form')->toHtml(),
        ));
        $this->addTab('websites', array(
            'label'     => Mage::helper('sdm_youtubefeed')->__('Websites'),
            'title'     => Mage::helper('sdm_youtubefeed')->__('Websites'),
            'content'   => $this->getLayout()->createBlock('sdm_youtubefeed/adminhtml_playlist_edit_tab_Websites')->toHtml(),
        ));
        $this->addTab('videos', array(
            'label' => Mage::helper('sdm_youtubefeed')->__('Videos'),
            'class' => 'ajax',
            'url'   => $this->getUrl('*/*/videos', array('_current' => true)),
         ));
         
        return parent::_beforeToHtml();
    }
}
