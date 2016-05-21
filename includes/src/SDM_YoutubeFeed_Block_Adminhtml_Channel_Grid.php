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
 * Channel grid
 */
class SDM_YoutubeFeed_Block_Adminhtml_Channel_Grid
     extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('channelGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir(Zend_Db_Select::SQL_DESC);
        $this->setSaveParametersInSession(true);
    }

    /**
     * Load collection
     *
     * @return SDM_YoutubeFeed_Block_Adminhtml_Channel_Grid
     */
    protected function _prepareCollection()
    {
        $this->setCollection(
            Mage::getModel('sdm_youtubefeed/channel')->getCollection()
        );
        return parent::_prepareCollection();
    }

    /**
     * Create columns
     *
     * @return SDM_YoutubeFeed_Block_Adminhtml_Channel_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => Mage::helper('sdm_youtubefeed')->__('ID'),
            'align'  =>'right',
            'width'  => '50px',
            'type'   => 'number',
            'index'  => 'id',
        ));
        $this->addColumn('identifier', array(
            'header' => Mage::helper('sdm_youtubefeed')->__('Identifier'),
            'index'  => 'identifier',
        ));
        $this->addColumn('name', array(
            'header' => Mage::helper('sdm_youtubefeed')->__('Name'),
            'index'  => 'name',
        ));
        $this->addColumn('position', array(
            'header' => Mage::helper('sdm_youtubefeed')->__('Position'),
            'align'  =>'right',
            'width'  => '50px',
            'type'   => 'number',
            'index'  => 'position',
        ));
        $this->addExportType('*/*/exportCsv', Mage::helper('sdm_youtubefeed')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sdm_youtubefeed')->__('Excel'));
        return parent::_prepareColumns();
    }

    /**
     * Get row link
     *
     * @param  SDM_YoutubeFeed_Model_Channel $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl("*/*/edit", array('id' => $row->getId()));
    }

    /**
     * Add mass actions
     *
     * @return SDM_YoutubeFeed_Block_Adminhtml_Channel_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids')
            ->setUseSelectAll(true)
            ->addItem('delete', array(
                 'label'   => Mage::helper('sdm_youtubefeed')->__('Delete'),
                 'url'     => $this->getUrl('*/sdm_youtubefeed_channel/massDelete'),
                 'confirm' => Mage::helper('sdm_youtubefeed')->__('Are you sure?')
            ));
        return $this;
    }
}
