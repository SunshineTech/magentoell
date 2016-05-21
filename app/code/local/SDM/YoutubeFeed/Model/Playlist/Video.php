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
 * Playlist/video relation model
 */
class SDM_YoutubeFeed_Model_Playlist_Video extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'sdm_youtubefeed_playlist_video';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'playlist_video';

    /**
     * Initialize model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sdm_youtubefeed/playlist_video');
    }

    /**
     * Create an association between a playlist and video
     *
     * @param integer     $playlistId
     * @param integer     $videoId
     * @param string|null $position
     *
     * @return void
     */
    public function create($playlistId, $videoId, $position = null)
    {
        $count = $this->getCollection()
            ->addFieldToFilter('playlist_id', $playlistId)
            ->addFieldToFilter('video_id', $videoId)
            ->count();
        if ($count != 0) {
            return;
        }
        $this->addData(array(
            'playlist_id' => $playlistId,
            'video_id'    => $videoId,
            'position'    => $position,
        ))->save();
    }
}
