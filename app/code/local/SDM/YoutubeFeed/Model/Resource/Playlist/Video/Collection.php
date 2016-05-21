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
 * Playlist/video relation resource collection model
 */
class SDM_YoutubeFeed_Model_Resource_Playlist_Video_Collection
     extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sdm_youtubefeed/playlist_video');
    }
}
