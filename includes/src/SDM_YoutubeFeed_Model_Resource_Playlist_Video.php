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
 * Playlist/video relation resource model
 */
class SDM_YoutubeFeed_Model_Resource_Playlist_Video
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sdm_youtubefeed/playlist_video', null);
    }

    /**
     * Update a video's position in playlist
     *
     * @param  integer $playlistId
     * @param  integer $videoId
     * @param  integer $position
     * @return boolean
     */
    public function updatePosition($playlistId, $videoId, $position)
    {
        $stmt = $this->_getWriteAdapter()
            ->prepare(
                'UPDATE ' . $this->getMainTable(). ' SET position = ? '
                . 'WHERE playlist_id = ? AND video_id = ?'
            );
        return $stmt->execute(array(
            $position,
            $playlistId,
            $videoId
        ));
    }
}
