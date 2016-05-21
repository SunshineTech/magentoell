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
 * Video index
 */
class SDM_YoutubeFeed_Block_Index extends Mage_Core_Block_Template
{
    /**
     * Collection of channels enabled in this store
     *
     * @return SDM_YoutubeFeed_Model_Resource_Channel_Collection
     */
    public function getChannels()
    {
        return Mage::getModel('sdm_youtubefeed/channel')->getCollection()
            ->addFieldToFilter('id', array('in' => $this->helper('sdm_youtubefeed')->getStoreChannelIds()))
            ->setOrder('position', Zend_Db_Select::SQL_ASC);
    }
}
