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
 * Channel view
 */
class SDM_YoutubeFeed_ChannelController
    extends Mage_Core_Controller_Front_Action
{
    /**
     * Display playlists and videos in a channel
     *
     * @return void
     */
    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id', false);
        if ($id === false) {
            return $this->norouteAction();
        }
        if (!in_array($id, Mage::helper('sdm_youtubefeed')->getStoreChannelIds())) {
            return $this->norouteAction();
        }
        $model = Mage::getModel('sdm_youtubefeed/channel')->load($id);
        if (!$model || !$model->getId()) {
            return $this->norouteAction();
        }
        Mage::register('current_channel', $model);
        $playlist = $this->_registerCurrentPlaylist();
        $this->loadLayout();
        if ($playlist !== false) {
            $title = $this->__('%s - %s Videos', $playlist->getName(), $model->getName());
        } else {
            $title = $this->__('%s Videos', $model->getName());
        }
        $this->getLayout()
            ->getBlock('head')
            ->setTitle($title);
        $this->renderLayout();
    }

    /**
     * Make not of the selected playlist, if applicable
     *
     * @return boolean|SDM_YoutubeFeed_Model_Playlist
     */
    protected function _registerCurrentPlaylist()
    {
        $id = $this->getRequest()->getParam('playlist', false);
        if ($id === false) {
            return false;
        }
        $model = Mage::getModel('sdm_youtubefeed/playlist')->load($id);
        if (!$model || !$model->getId()) {
            $model = false;
        }
        Mage::register('current_playlist', $model);
        return $model;
    }
}
