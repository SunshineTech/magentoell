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
 * Video grid
 */
class SDM_YoutubeFeed_Block_Adminhtml_Video_Grid
     extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('videoGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir(Zend_Db_Select::SQL_DESC);
        $this->setSaveParametersInSession(true);
    }

    /**
     * Load collection
     *
     * @return SDM_YoutubeFeed_Block_Adminhtml_Video_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('sdm_youtubefeed/video')->getCollection();
        $collection->getSelect()->join(
            array('pv' => $collection->getTable('sdm_youtubefeed/playlist_video')),
            'pv.video_id=main_table.id',
            array('playlist_id')
        );
        $collection->getSelect()->join(
            array('p' => $collection->getTable('sdm_youtubefeed/playlist')),
            'p.id=pv.playlist_id',
            array('channel_id')
        );
        $collection->getSelect()->join(
            array('c' => $collection->getTable('sdm_youtubefeed/channel')),
            'c.id=p.channel_id',
            null
        );
        $collection->getSelect()->group('main_table.id');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Create columns
     *
     * @return SDM_YoutubeFeed_Block_Adminhtml_Video_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'       => Mage::helper('sdm_youtubefeed')->__('ID'),
            'align'        =>'right',
            'width'        => '50px',
            'type'         => 'number',
            'index'        => 'id',
            'filter_index' => 'main_table.id'
        ));
        $this->addColumn('identifier', array(
            'header'       => Mage::helper('sdm_youtubefeed')->__('Identifier'),
            'index'        => 'identifier',
            'filter_index' => 'main_table.identifier'
        ));
        $this->addColumn('name', array(
            'header'       => Mage::helper('sdm_youtubefeed')->__('Name'),
            'index'        => 'name',
            'filter_index' => 'main_table.name'
        ));
        $this->addColumn('status', array(
            'header'  => Mage::helper('sdm_youtubefeed')->__('Status'),
            'type'    => 'options',
            'options' => array(
                SDM_YoutubeFeed_Model_Video::STATUS_ENABLED  => Mage::helper('sdm_youtubefeed')->__('Enabled'),
                SDM_YoutubeFeed_Model_Video::STATUS_DISABLED => Mage::helper('sdm_youtubefeed')->__('Disabled')
            ),
            'index'   => 'status',
        ));
        $this->addColumn('featured', array(
            'header'  => Mage::helper('sdm_youtubefeed')->__('Featured'),
            'type'    => 'options',
            'options' => array(
                SDM_YoutubeFeed_Model_Video::FEATURED_YES  => Mage::helper('sdm_youtubefeed')->__('Yes'),
                SDM_YoutubeFeed_Model_Video::FEATURED_NO => Mage::helper('sdm_youtubefeed')->__('No')
            ),
            'index'   => 'featured',
        ));
        $this->addColumn('channel_id', array(
            'header'  => Mage::helper('sdm_youtubefeed')->__('Channel'),
            'type'    => 'options',
            'options' => Mage::getResourceModel('sdm_youtubefeed/channel_collection')
                ->loadData()
                ->toOptionArray(),
            'index'   => 'channel_id',
        ));
        $this->addColumn('playlist_id', array(
            'header'  => Mage::helper('sdm_youtubefeed')->__('Playlist'),
            'type'    => 'options',
            'options' => Mage::getResourceModel('sdm_youtubefeed/playlist_collection')
                ->loadData()
                ->toOptionArray(),
            'index'   => 'playlist_id',
        ));
        $this->addExportType('*/*/exportCsv', Mage::helper('sdm_youtubefeed')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sdm_youtubefeed')->__('Excel'));
        return parent::_prepareColumns();
    }

    /**
     * Get row link
     *
     * @param  SDM_YoutubeFeed_Model_Video $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl("*/*/edit", array('id' => $row->getId()));
    }

    /**
     * Add mass actions
     *
     * @return SDM_YoutubeFeed_Block_Adminhtml_Video_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids')
            ->setUseSelectAll(true)
            ->addItem('enable', array(
                 'label'   => Mage::helper('sdm_youtubefeed')->__('Enable'),
                 'url'     => $this->getUrl('*/sdm_youtubefeed_video/massEnable'),
            ))
            ->addItem('disable', array(
                 'label'   => Mage::helper('sdm_youtubefeed')->__('Disable'),
                 'url'     => $this->getUrl('*/sdm_youtubefeed_video/massDisable'),
            ));
        return $this;
    }
}
