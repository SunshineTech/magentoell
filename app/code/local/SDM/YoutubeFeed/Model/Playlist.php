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
 * Playlist model
 */
class SDM_YoutubeFeed_Model_Playlist extends Mage_Core_Model_Abstract
{
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED  = 1;
    
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'sdm_youtubefeed_playlist';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'playlist';

    /**
     * Initialize model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sdm_youtubefeed/playlist');
    }

    /**
     * Create the video if it doesn't already exist
     *
     * @param  Google_Service_YouTube_Video $video
     * @return boolean|SDM_YoutubeFeed_Model_Video
     */
    public function addVideo(Google_Service_YouTube_Video $video)
    {
        $identifier = $video->getId();
        $model = $this->getVideo($identifier);
        // Create the video if it doesn't exist yet
        if (!$model->getId()) {
            $model = Mage::getModel('sdm_youtubefeed/video');
            $time = new DateTime('@0');
            $time->add(
                new DateInterval($video->getContentDetails()->getDuration())
            );
            $model->addData(array(
                'identifier'   => $identifier,
                'name'         => $video->getSnippet()->getTitle(),
                'description'  => $video->getSnippet()->getDescription(),
                'status'       => Mage::getStoreConfig(SDM_YoutubeFeed_Helper_Data::XML_PATH_API_VIDEO_IMPORT_STATUS),
                'featured'     => SDM_YoutubeFeed_Model_Video::FEATURED_NO,
                'published_at' => Mage::getSingleton('core/date')
                    ->date(null, $video->getSnippet()->getPublishedAt()),
                'duration'     => $time->getTimestamp(),
            ));
            $model->save();
        }
        Mage::getModel('sdm_youtubefeed/playlist_video')->create(
            $this->getId(),
            $model->getId(),
            Mage::getStoreConfig(SDM_YoutubeFeed_Helper_Data::XML_PATH_API_VIDEO_IMPORT_POSITION)
        );
        // Add more data/update data
        $model
            ->setViews($video->getStatistics()->getViewCount())
            ->save();
        return $model;
    }

    /**
     * Get video by identifier for this channel
     *
     * @param  string $identifier
     * @return boolean|SDM_YoutubeFeed_Model_Video
     */
    public function getVideo($identifier)
    {
        return Mage::getModel('sdm_youtubefeed/video')->getCollection()
            ->addFieldToFilter('identifier', $identifier)
            ->getFirstItem();
    }
}
