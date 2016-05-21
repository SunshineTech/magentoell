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
 * Playlist grid container
 */
class SDM_YoutubeFeed_Block_Adminhtml_Playlist
     extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize grid
     */
    public function __construct()
    {
        $this->_controller     = 'adminhtml_playlist';
        $this->_blockGroup     = 'sdm_youtubefeed';
        $this->_headerText     = Mage::helper('sdm_youtubefeed')->__('Manage Playlists');
        $this->_addButtonLabel = Mage::helper('sdm_youtubefeed')->__('Add Playlist');
        parent::__construct();
    }
}
