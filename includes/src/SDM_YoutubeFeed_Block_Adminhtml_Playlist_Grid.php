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
 * Playlist grid
 */
class SDM_YoutubeFeed_Block_Adminhtml_Playlist_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('playlistGrid');
        $this->setDefaultSort('main_table.id');
        $this->setDefaultDir(Zend_Db_Select::SQL_DESC);
        $this->setSaveParametersInSession(true);
    }

    /**
     * Load collection
     *
     * @return SDM_YoutubeFeed_Block_Adminhtml_Playlist_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('sdm_youtubefeed/playlist')->getCollection();
        foreach ($collection as $key => $value) {
            if ($value->getStoreId() && $value->getStoreId() != 0) {
                $value->setStoreId(explode(',', $value->getStoreId()));
            } else {
                $value->setStoreId(array('0'));
            }
        }
        $collection->getSelect()->joinLeft(
            array('pv' => $collection->getTable('sdm_youtubefeed/playlist_video')),
            'pv.playlist_id=main_table.id',
            array(
                'video_count' => new Zend_Db_Expr('COUNT(' . Zend_Db_Select::SQL_DISTINCT . ' pv.video_id)'),
            )
        );
        $collection->getSelect()->group('main_table.id');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Create columns
     *
     * @return SDM_YoutubeFeed_Block_Adminhtml_Playlist_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'       => Mage::helper('sdm_youtubefeed')->__('ID'),
            'align'        => 'right',
            'width'        => '50px',
            'type'         => 'number',
            'index'        => 'id',
            'filter_index' => 'main_table.id',
        ));
        $this->addColumn('identifier', array(
            'header'       => Mage::helper('sdm_youtubefeed')->__('Identifier'),
            'index'        => 'identifier',
            'filter_index' => 'main_table.identifier',
        ));
        /*$websiteOptions = Mage::getSingleton('adminhtml/system_store')
        ->getWebsiteValuesForForm(false, true);
        unset($websiteOptions[0]);  // Remove Admin store*/
        $this->addColumn('websites',
            array(
                'header'                    => Mage::helper('sdm_youtubefeed')->__('Websites'),
                'align'                     => 'right',
                'index'                     => 'websites',
                'type'                      => 'options',
                'options'                   => Mage::helper('sdm_core')->getAssociativeEllisonSystemCodes(true),
                'renderer'                  => 'SDM_YoutubeFeed_Block_Adminhtml_Playlist_Renderer_Website',
                'filter_condition_callback' => array($this, '_filterWebsiteIds'), // For filtering
            )
        );

        $this->addColumn('name', array(
            'header'       => Mage::helper('sdm_youtubefeed')->__('Name'),
            'index'        => 'name',
            'filter_index' => 'main_table.name',
        ));
        $this->addColumn('channel_id', array(
            'header'  => Mage::helper('sdm_youtubefeed')->__('Channel'),
            'type'    => 'options',
            'options' => Mage::getResourceModel('sdm_youtubefeed/channel_collection')
                ->loadData()
                ->toOptionArray(),
            'index'   => 'channel_id',
        ));
        $this->addColumn('position', array(
            'header' => Mage::helper('sdm_youtubefeed')->__('Position'),
            'align'  => 'right',
            'width'  => '50px',
            'type'   => 'number',
            'index'  => 'position',
        ));
        $this->addColumn('playlist_status', array(
            'header'  => Mage::helper('sdm_youtubefeed')->__('Status'),
            'type'    => 'options',
            'options' => array(
                SDM_YoutubeFeed_Model_Playlist::STATUS_ENABLED  => Mage::helper('sdm_youtubefeed')->__('Enabled'),
                SDM_YoutubeFeed_Model_Playlist::STATUS_DISABLED => Mage::helper('sdm_youtubefeed')->__('Disabled'),
            ),
            'index'   => 'playlist_status',
        ));
        $this->addColumn('video_count', array(
            'header' => Mage::helper('sdm_youtubefeed')->__('Videos'),
            'index'  => 'video_count',
        ));
        $this->addExportType('*/*/exportCsv', Mage::helper('sdm_youtubefeed')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sdm_youtubefeed')->__('Excel'));
        return parent::_prepareColumns();
    }

    /**
     * Get row link
     *
     * @param  SDM_YoutubeFeed_Model_Playlist $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl("*/*/edit", array('id' => $row->getId()));
    }

    /**
     * Allows the website IDs to be filtered correctly in the grid by joining
     * the date table
     *
     * @param SDM_Gmapstrlocator_Model_Resource_Item_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column           $column
     *
     * @return void
     */
    protected function _filterWebsiteIds($collection, $column)
    {
       /* $store = Mage::helper('sdm_core')->getAssociativeEllisonSystemCodes(true);
        echo "<pre>";print_r($store);die;
        $this->addFilter('', array('in' => $store));    
        return $this;*/
    }
}
