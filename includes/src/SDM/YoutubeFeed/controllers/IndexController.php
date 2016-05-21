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
 * Index view
 */
class SDM_YoutubeFeed_IndexController
    extends Mage_Core_Controller_Front_Action
{
    /**
     * Index of videos, playlists, and channels
     *
     * @return void
     */
    public function indexAction()
    {
        $channels = Mage::helper('sdm_youtubefeed')->getStoreChannelIds();
        if (count($channels) == 1) {
            $this->_redirect('*/channel/view', array('id' => $channels[0]));
        } else {
            $this->loadLayout();
            $this->renderLayout();
        }
    }
}
