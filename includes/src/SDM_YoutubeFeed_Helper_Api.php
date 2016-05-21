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
 * Interface with Youtube's API
 */
class SDM_YoutubeFeed_Helper_Api extends Mage_Core_Helper_Abstract
{
    const XML_PATH_API_CLIENT_EMAIL = 'sdm_youtubefeed/api/client_email';
    const XML_PATH_API_KEY_FILE     = 'sdm_youtubefeed/api/key_file';

    const APPLICATION_NAME = 'Magento_SDM_YoutubeFeed';

    const REGISTRY_SERVICE = 'SDM_YoutubeFeed_API_Service';

    /**
     * Counter of Youtube API quota cost used
     *
     * @var integer
     */
    protected $_quotaConsumed = 0;

    /**
     * Get a list of playlists from a channel
     *
     * Quota cost per call: method:1 + id:0 + snippet:2 = 3
     *
     * @param  string $channelId
     * @return Google_Service_YouTube_PlaylistListResponse
     */
    public function getPlaylists($channelId)
    {
        $this->_quotaConsumed += 3;
        return $this->getService()
            ->playlists
            ->listPlaylists('id,snippet,status', array(
                'channelId'  => $channelId,
                'maxResults' => 50,
            ));
    }

    /**
     * Get a list of items from a playlist
     *
     * Quota cost per call: method:1 + snippet:2 + status:2 + contentDetails:2 = 7
     *
     * @param  string         $playlistId
     * @param  string|boolean $nextPageToken
     * @return Google_Service_YouTube_PlaylistItemListResponse
     */
    public function getPlaylistItems($playlistId, $nextPageToken = false)
    {
        $this->_quotaConsumed += 7;
        $options = array(
            'playlistId' => $playlistId,
            'maxResults' => 50,
        );
        if ($nextPageToken !== false) {
            $options['pageToken'] = $nextPageToken;
        }
        return $this->getService()
            ->playlistItems
            ->listPlaylistItems(
                'snippet,status,contentDetails', $options
            );
    }

    /**
     * Get videos from a playlist
     *
     * @param  string         $playlistId
     * @param  string|boolean $nextPageToken
     * @return Google_Service_YouTube_VideoListResponse
     */
    public function getPlaylistVideos($playlistId, $nextPageToken = false)
    {
        $items = $this->getPlaylistItems($playlistId, $nextPageToken);
        $ids = array();
        foreach ($items as $item) {
            if ($item->getStatus()->getPrivacyStatus() == 'private') {
                continue;
            }
            $ids[] = $item->getContentDetails()->getVideoId();
        }
        return array(
            'videos' => $this->getVideos($ids),
            'next'   => $items->getNextPageToken()
        );
    }

    /**
     * Get details about a youtube video
     *
     * Quota cost per call: method:1 + snippet:2 + statistics:2 + contentDetails:2 = 7
     *
     * @param  array $videoIds
     * @return Google_Service_YouTube_VideoListResponse
     */
    public function getVideos(array $videoIds)
    {
        $this->_quotaConsumed += 7;
        return $this->getService()
            ->videos
            ->listVideos(
                'contentDetails,snippet,statistics', array(
                    'id' => implode(',', $videoIds),
                    'maxResults' => 50,
                )
            );
    }

    /**
     * Get the image url for a given channel
     *
     * Quota cost per call: method:1 + snippet:2 = 3
     *
     * @param  string $identifier
     * @return string|null
     */
    public function getChannelImageUrl($identifier)
    {
        $this->_quotaConsumed += 3;
        $channels = $this->getService()
            ->channels
            ->listChannels(
                'snippet',
                array('id' => $identifier)
            );
        foreach ($channels as $channel) {
            return $channel->getSnippet()
                ->getThumbnails()
                ->getHigh()
                ->getUrl();
        }
        return null;
    }

    /**
     * Authenticate with Youtube API
     *
     * @return Google_Service_YouTube
     */
    public function getService()
    {
        /**
         * @var Google_Service_YouTube
         */
        $service = Mage::registry(self::REGISTRY_SERVICE);
        if (!$service) {
            $client = new Google_Client();
            $client->setApplicationName('Magento_SDM_YoutubeFeed');
            $service = new Google_Service_YouTube($client);
            $cred = new Google_Auth_AssertionCredentials(
                Mage::getStoreConfig(self::XML_PATH_API_CLIENT_EMAIL),
                array(Google_Service_YouTube::YOUTUBE),
                file_get_contents($this->_getKeyFile())
            );
            $client->setAssertionCredentials($cred);
            if ($client->getAuth()->isAccessTokenExpired()) {
                $client->getAuth()->refreshTokenWithAssertion($cred);
            }
            if (!$client->getAccessToken()) {
                Mage::throwException('Failed to authenticate with Youtube API');
            }
            Mage::register(self::REGISTRY_SERVICE, $service);
        }
        return $service;
    }

    /**
     * Get the full name of the key file
     *
     * @return string
     */
    protected function _getKeyFile()
    {
        return Mage::getBaseDir(SDM_YoutubeFeed_Model_Adminhtml_System_Config_Backend_File_Key::UPLOAD_ROOT)
            . '/' . SDM_YoutubeFeed_Model_Adminhtml_System_Config_Backend_File_Key::UPLOAD_DIR
            . '/' . Mage::getStoreConfig(self::XML_PATH_API_KEY_FILE);
    }

    /**
     * Get the estimated quota cost used so far
     *
     * @return integer
     */
    public function getQuotaConsumed()
    {
        return $this->_quotaConsumed;
    }
}
