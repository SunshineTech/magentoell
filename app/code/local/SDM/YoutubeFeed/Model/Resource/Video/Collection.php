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
 * Video resource collection model
 */
class SDM_YoutubeFeed_Model_Resource_Video_Collection
     extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected $_isPlaylistJoined = false;

    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sdm_youtubefeed/video');
    }

    /**
     * Filter collection by a channel
     *
     * @param  SDM_YoutubeFeed_Model_Channel $channel
     * @return SDM_YoutubeFeed_Model_Resource_Video_Collection
     */
    public function applyChannelFilter(SDM_YoutubeFeed_Model_Channel $channel)
    {
        $this->_joinPlaylist();
        $this->addFieldToFilter('p.channel_id', $channel->getId());
        return $this;
    }

    /**
     * Filter collection by a playlist
     *
     * @param  SDM_YoutubeFeed_Model_Playlist $playlist
     * @return SDM_YoutubeFeed_Model_Resource_Video_Collection
     */
    public function applyPlaylistFilter(SDM_YoutubeFeed_Model_Playlist $playlist)
    {
        $this->_joinPlaylist();
        $this->addFieldToFilter('p.id', $playlist->getId());
        return $this;
    }

    /**
     * Filter collection by a search string
     *
     * @param  string $search
     * @return SDM_YoutubeFeed_Model_Resource_Video_Collection
     */
    public function applySearchFilter($search)
    {
        $this->addFieldToFilter(
            array('main_table.name', 'main_table.description'),
            array(
                array('like' => '%' . $search . '%'),
                array('like' => '%' . $search . '%')
            )
        );
        return $this;
    }

    /**
     * Inner join the playlist table
     *
     * @return SDM_YoutubeFeed_Model_Resource_Video_Collection
     */
    protected function _joinPlaylist()
    {
        if (!$this->_isPlaylistJoined) {
            $this->getSelect()->join(
                array('pv' => $this->getTable('sdm_youtubefeed/playlist_video')),
                'pv.video_id=main_table.id',
                array()
            );
            $this->getSelect()->join(
                array('p' => $this->getTable('sdm_youtubefeed/playlist')),
                'p.id=pv.playlist_id',
                array()
            );
            $this->_isPlaylistJoined = true;
        }
        return $this;
    }

    /**
     * Filter collection by designer
     *
     * @param  SDM_Taxonomy_Model_Item $designer
     * @return SDM_YoutubeFeed_Model_Resource_Video_Collection
     */
    public function addDesignerToFilter(SDM_Taxonomy_Model_Item $designer)
    {
        $this
            ->addFieldToFilter(
                array('designer', 'designer', 'designer', 'designer'),
                array(
                    $designer->getCode(),
                    array('like' => '%,' . $designer->getCode()),
                    array('like' => '%,' . $designer->getCode() . ',%'),
                    array('like' => $designer->getCode() . ',%')
                )
            );
        return $this;
    }

    /**
     * Fixes pagination
     *
     * @return string
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        if (count($this->getSelect()->getPart(Zend_Db_Select::GROUP)) > 0) {
            $countSelect->reset(Zend_Db_Select::GROUP);
            $countSelect->distinct(true);
            $group = $this->getSelect()->getPart(Zend_Db_Select::GROUP);
            $countSelect->columns('COUNT(DISTINCT ' . implode(', ', $group) . ')');
        } else {
            $countSelect->columns('COUNT(*)');
        }
        return $countSelect;
    }
}
