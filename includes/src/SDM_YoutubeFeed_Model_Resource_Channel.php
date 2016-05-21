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
 * Channel resource model
 */
class SDM_YoutubeFeed_Model_Resource_Channel
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sdm_youtubefeed/channel', 'id');
    }

    /**
     * Get playlists in this channel with videos
     *
     * @param  integer $channelId
     * @return SDM_YoutubeFeed_Model_Resource_Playlist_Collection
     */
    public function getPlaylistsWithVideos($channelId)
    {
        $collection = Mage::getModel('sdm_youtubefeed/playlist')->getCollection()
            ->addFieldToFilter('channel_id', $channelId)
            ->setOrder('position', Zend_Db_Select::SQL_ASC);
        $collection->getSelect()->join(
            array('pv' => $this->getTable('sdm_youtubefeed/playlist_video')),
            'pv.playlist_id=main_table.id',
            array()
        );
        $collection->getSelect()->join(
            array('v' => $this->getTable('sdm_youtubefeed/video')),
            'v.id=pv.video_id',
            array()
        );
        $collection->addFieldToFilter('v.status', SDM_YoutubeFeed_Model_Video::STATUS_ENABLED);
        $collection->addFieldToFilter('playlist_status', SDM_YoutubeFeed_Model_Playlist::STATUS_ENABLED);
        $collection->getSelect()->group('main_table.id');
        $collection->getSelect()->having('COUNT(1) >= 1');
        return $collection;
    }

    /**
     * Gets a video that is flagged as featured.  If multiples are found the one
     * with the newest publish date is used
     *
     * @param  integer $channelId
     * @return boolean|SDM_YoutubeFeed_Model_Video
     */
    public function getFeaturedVideo($channelId)
    {
        $collection = Mage::getModel('sdm_youtubefeed/video')->getCollection()
            ->addFieldToFilter('featured', SDM_YoutubeFeed_Model_Video::FEATURED_YES)
            ->addFieldToFilter('status', SDM_YoutubeFeed_Model_Video::STATUS_ENABLED);
        $collection->getSelect()->join(
            array('pv' => $this->getTable('sdm_youtubefeed/playlist_video')),
            'pv.video_id=main_table.id',
            array()
        );
        $collection->getSelect()->join(
            array('p' => $this->getTable('sdm_youtubefeed/playlist')),
            'p.id=pv.playlist_id',
            array()
        );
        $collection
            ->addFieldToFilter('p.channel_id', $channelId)
            ->setPageSize(1)
            ->setOrder('published_at', Zend_Db_Select::SQL_DESC);
        return $collection->getFirstItem();
    }
}
