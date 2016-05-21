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
 * Channel view featured block
 */
class SDM_YoutubeFeed_Block_Channel_View_Featured
    extends SDM_YoutubeFeed_Block_Channel_Abstract
{
    /**
     * Gets a video that is flagged as featured.  If multiples are found the one
     * with the newest publish date is used
     *
     * @return boolean|SDM_YoutubeFeed_Model_Video
     */
    public function getFeaturedVideo()
    {
        return $this->getChannel()->getFeaturedVideo();
    }
}
