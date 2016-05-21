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
 * Cron operations
 */
class SDM_YoutubeFeed_Model_Cron
{
    /**
     * Update youtube videos and playlists
     *
     * @return string
     */
    public function update()
    {
        $quota = 0;
        $channels = Mage::getModel('sdm_youtubefeed/channel')->getCollection();
        foreach ($channels as $channel) {
            $quota += $this->updateChannel($channel);
        }
        return 'Estimated quota consumed: ' . $quota;
    }

    /**
     * Update videos for given channel
     *
     * @param  SDM_YoutubeFeed_Model_Channel $channel
     * @return integer
     */
    public function updateChannel(SDM_YoutubeFeed_Model_Channel $channel)
    {
        $helper = Mage::helper('sdm_youtubefeed/api');
        /**
         * @var Google_Service_YouTube_PlaylistListResponse
         */
        $playlists = $helper->getPlaylists($channel->getIdentifier());
        foreach ($playlists as $playlist) {
            $channel->addPlaylist($playlist);
        }
        $playlists = Mage::getModel('sdm_youtubefeed/playlist')->getCollection()
            ->addFieldToFilter('channel_id', $channel->getId());
        foreach ($playlists as $playlist) {
            $helper = $this->_addVideos($helper, $playlist);
        }
        return $helper->getQuotaConsumed();
    }

    /**
     * Paginate through videos in playlist
     *
     * @param SDM_YoutubeFeed_Helper_Api     $helper
     * @param SDM_YoutubeFeed_Model_Playlist $playlist
     * @param boolean                        $next
     *
     * @return SDM_YoutubeFeed_Helper_Api
     */
    protected function _addVideos(
        SDM_YoutubeFeed_Helper_Api $helper,
        SDM_YoutubeFeed_Model_Playlist $playlist,
        $next = false
    ) {
        $videos = $helper->getPlaylistVideos($playlist->getIdentifier(), $next);
        foreach ($videos['videos'] as $video) {
            $playlist->addVideo($video);
        }
        if ($videos['next']) {
            $helper = $this->_addVideos($helper, $playlist, $videos['next']);
        }
        return $helper;
    }
}
