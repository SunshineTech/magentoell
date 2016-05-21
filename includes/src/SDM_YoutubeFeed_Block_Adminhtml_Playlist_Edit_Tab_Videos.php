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
 * List videos for this playlist in a grid
 */
class SDM_YoutubeFeed_Block_Adminhtml_Playlist_Edit_Tab_Videos
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('playlistVideosGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir(Zend_Db_Select::SQL_DESC);
        $this->setUseAjax(true);
    }

    /**
     * Load video collection for this playlist
     *
     * @return SDM_YoutubeFeed_Block_Adminhtml_Playlist_Edit_Tab_Videos
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('sdm_youtubefeed/playlist_video')
            ->getCollection()
            ->addFieldToFilter('playlist_id', Mage::registry('current_playlist')->getId());
        $collection->getSelect()->join(
            array('v' => $collection->getTable('sdm_youtubefeed/video')),
            'v.id=main_table.video_id',
            array('identifier', 'name')
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add columns to tab
     *
     * @return SDM_YoutubeFeed_Block_Adminhtml_Playlist_Edit_Tab_Videos
     */
    protected function _prepareColumns()
    {
        $this->addColumn('video_id', array(
            'header' => Mage::helper('sdm_youtubefeed')->__('ID'),
            'align'  =>'right',
            'width'  => '50px',
            'type'   => 'number',
            'index'  => 'video_id',
        ));
        $this->addColumn('video_identifier', array(
            'header' => Mage::helper('sdm_youtubefeed')->__('Identifier'),
            'index'  => 'identifier',
        ));
        $this->addColumn('video_name', array(
            'header' => Mage::helper('sdm_youtubefeed')->__('Name'),
            'index'  => 'name',
        ));
        $this->addColumn('video_position', array(
            'header'   => Mage::helper('sdm_youtubefeed')->__('Position'),
            'align'    =>'right',
            'width'    => '50px',
            'type'     => 'number',
            'renderer' => 'sdm_youtubefeed/adminhtml_playlist_video_edit_tab_videos_grid_renderer_position',
        ));
        return parent::_prepareColumns();
    }

    /**
     * Get url for a given video
     *
     * @param  SDM_YoutubeFeed_Model_Playlist_Video $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/sdm_youtubefeed_video/edit', array('id' => $row->getVideoId()));
    }

    /**
     * URL for this grid (ajax)
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/videos', array('_current' => true));
    }
}
