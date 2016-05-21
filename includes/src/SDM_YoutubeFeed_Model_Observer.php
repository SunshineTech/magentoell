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
 * Observer operations
 */
class SDM_YoutubeFeed_Model_Observer
{
    /**
     * Update channel image url
     *
     * @param  Varien_Event_Observer $observer
     * @return boolean
     */
    public function updateChannelImageUrl(Varien_Event_Observer $observer)
    {
        /**
         * @var SDM_YoutubeFeed_Model_Channel
         */
        $channel = $observer->getChannel();
        if ($channel->getIdentifier()) {
            $identifier = $channel->getIdentifier();
        } elseif (Mage::app()->getRequest()->getPost('identifier')) {
            $identifier = Mage::app()->getRequest()->getPost('identifier');
        } else {
            return false;
        }
        $channel->setImageUrl(Mage::helper('sdm_youtubefeed/api')
                    ->getChannelImageUrl($identifier));
        return true;
    }
}
