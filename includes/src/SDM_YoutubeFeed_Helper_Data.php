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
 * Data helper
 */
class SDM_YoutubeFeed_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_API_KEY_FILE              = 'sdm_youtubefeed/general/channel';
    const XML_PATH_API_VIDEO_IMPORT_STATUS   = 'sdm_youtubefeed/api/video_import_status';
    const XML_PATH_API_VIDEO_IMPORT_POSITION = 'sdm_youtubefeed/api/video_import_position';

    const YOUTUBE_IMAGE_TYPE_LARGE = 0;
    const YOUTUBE_IMAGE_TYPE_SMALL = 2;

    /**
     * Get enabled channels for this store
     *
     * @return array
     */
    public function getStoreChannelIds()
    {
        return explode(',', Mage::getStoreConfig(self::XML_PATH_API_KEY_FILE));
    }

    /**
     * Get the youtube image for a video
     *
     * @param  string|SDM_YoutubeFeed_Model_Video $video
     * @param  integer                            $type
     * @return string
     */
    public function getVideoImage($video, $type = self::YOUTUBE_IMAGE_TYPE_LARGE)
    {
        if ($video instanceof SDM_YoutubeFeed_Model_Video) {
            $video = $video->getIdentifier();
        }
        return '//img.youtube.com/vi/' . $video . '/' . $type . '.jpg';
    }

    /**
     * Get the youtube iframe for a video
     *
     * @param  string|SDM_YoutubeFeed_Model_Video $video
     * @return string
     */
    public function getVideoIframe($video)
    {
        if ($video instanceof SDM_YoutubeFeed_Model_Video) {
            $video = $video->getIdentifier();
        }
        return '<iframe id="youtube-' . $video .'" src="https://www.youtube.com/embed/' . $video
            . ''.'?enablejsapi=1&html5=1'.'" frameborder="0" allowfullscreen></iframe>';
    }

    /**
     * Convert seconds to a readable time
     *
     * @param  integer $seconds
     * @return string
     */
    public function getReadableDuration($seconds)
    {
        $times = $values = array();
        $hours = floor($seconds / 3600) % 24;
        if ($hours > 0) {
            $times[]  = '%s hour' . ($hours == 1 ? '' : 's');
            $values[] = $hours;
        }
        $minutes = floor($seconds / 60) % 60;
        if ($minutes > 0) {
            $values[] = $minutes;
            $times[]  = '%s minute' . ($minutes == 1 ? '' : 's');
        }
        $seconds  = floor($seconds % 60);
        $times[]  = '%s second' . ($seconds == 1 ? '' : 's');
        $values[] = $seconds;
        return call_user_func_array(
            // call $this->__()
            array($this, '__'),
            // with these arguments, i.e.
            //   $this->__('%s minutes %s seconds', 4, 3)
            array_merge(array(implode(' ', $times)), $values)
        );
    }
}
