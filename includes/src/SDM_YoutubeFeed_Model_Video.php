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
 * Video model
 */
class SDM_YoutubeFeed_Model_Video extends Mage_Core_Model_Abstract
{
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED  = 1;
    const FEATURED_NO     = 0;
    const FEATURED_YES    = 1;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'sdm_youtubefeed_video';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'video';

    /**
     * Initialize model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sdm_youtubefeed/video');
    }
}
