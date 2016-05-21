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
 * Abstract channel block
 */
class SDM_YoutubeFeed_Block_Channel_Abstract
    extends Mage_Core_Block_Template
{
    /**
     * Get the current channel
     *
     * @return SDM_YoutubeFeed_Model_Channel
     */
    public function getChannel()
    {
        return Mage::registry('current_channel');
    }
}
