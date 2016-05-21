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
 * Channel model
 */
class SDM_YoutubeFeed_Model_Channel extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'sdm_youtubefeed_channel';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'channel';

    /**
     * Initialize model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sdm_youtubefeed/channel');
    }

    /**
     * Create the playlist if it doesn't already exist
     *
     * @param  Google_Service_YouTube_Playlist $playlist
     * @return SDM_YoutubeFeed_Model_Playlist
     */
    public function addPlaylist(Google_Service_YouTube_Playlist $playlist)
    {
        $identifier = $playlist->getId();
        $model = $this->getPlaylist($identifier);
        if (!$model->getId()) {
            $model = Mage::getModel('sdm_youtubefeed/playlist');
            $model->addData(array(
                'identifier' => $identifier,
                'channel_id' => $this->getId(),
                'name'       => $playlist->getSnippet()->getTitle()
            ));
            $model->save();
        }
        return $model;
    }

    /**
     * Get playlist by identifier for this channel
     *
     * @param  string $identifier
     * @return boolean|SDM_YoutubeFeed_Model_Playlist
     */
    public function getPlaylist($identifier)
    {
        return Mage::getModel('sdm_youtubefeed/playlist')->getCollection()
            ->addFieldToFilter('identifier', $identifier)
            ->addFieldToFilter('channel_id', $this->getId())
            ->getFirstItem();
    }

    /**
     * Get the playlists for this channel
     *
     * @return SDM_YoutubeFeed_Model_Resource_Playlist_Collection
     */
    public function getPlaylistsWithVideos()
    {
        return $this->getResource()
            ->getPlaylistsWithVideos($this->getId());
    }

    /**
     * Gets a video that is flagged as featured.  If multiples are found the one
     * with the newest publish date is used
     *
     * @return boolean|SDM_YoutubeFeed_Model_Video
     */
    public function getFeaturedVideo()
    {
        return $this->getResource()->getFeaturedVideo($this->getId());
    }
}
