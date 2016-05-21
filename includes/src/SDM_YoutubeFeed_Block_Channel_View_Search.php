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
 * Channel view search block
 */
class SDM_YoutubeFeed_Block_Channel_View_Search
    extends SDM_YoutubeFeed_Block_Channel_Abstract
{
    /**
     * Get the controller action for the form
     *
     * @return string
     */
    public function getFormAction()
    {
        $params = array(
            'id' => Mage::registry('current_channel')->getId()
        );
        $playlist = Mage::registry('current_playlist');
        if ($playlist) {
            $params['playlist'] = $playlist->getId();
        }
        return $this->getUrl('*/*/*', $params);
    }
}
