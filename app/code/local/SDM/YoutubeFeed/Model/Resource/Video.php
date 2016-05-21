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
 * Video resource model
 */
class SDM_YoutubeFeed_Model_Resource_Video
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sdm_youtubefeed/video', 'id');
    }
}
