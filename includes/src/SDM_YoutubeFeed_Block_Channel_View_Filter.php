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
 * Channel view search block
 */
class SDM_YoutubeFeed_Block_Channel_View_Filter
    extends SDM_YoutubeFeed_Block_Channel_Abstract
{
    /**
     * Get the playlists for this channel
     *
     * @return SDM_YoutubeFeed_Model_Resource_Playlist_Collection
     */
    public function getPlaylists()
    {
        return $this->getChannel()->getPlaylistsWithVideos();
    }

    /**
     * Gets the selected playlist id, if applicable
     *
     * @return boolean|integer
     */
    public function getCurrentPlaylistId()
    {
        $model = Mage::registry('current_playlist');
        if (!$model) {
            return false;
        }
        return $model->getId();
    }
}
