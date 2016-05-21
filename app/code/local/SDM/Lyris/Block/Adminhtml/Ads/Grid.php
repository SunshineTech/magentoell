<?php
/**
 * Separation Degrees One
 *
 * Lyris Newsletter Management
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Lyris
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Ads Grid
 */
class SDM_Lyris_Block_Adminhtml_Ads_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('sdm_lyris_ads_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Load collection
     * @return SDM_Lyris_Block_Adminhtml_Ads_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('sdm_lyris/ads')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Create Columns
     * @return SDM_Lyris_Block_Adminhtml_Ads_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('sdm_lyris')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'id',
            ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('sdm_lyris')->__('Title'),
            'align'     =>'left',
            'index'     => 'title',
            ));

        $this->addColumn('image', array(
            'header'    => Mage::helper('sdm_lyris')->__('Image'),
            'align'     =>'left',
            'index'     => 'image',
            ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('sdm_lyris')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(
                1 => 'Enabled',
                2 => 'Disabled',
                ),
            ));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('sdm_lyris')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('sdm_lyris')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                        )
                    ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
                ));
            return parent::_prepareColumns();
    }

    /**
     * Get row link
     * @param  SDM_Lyris_Model_Ads $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}
