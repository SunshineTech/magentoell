<?php
/**
 * Separation Degrees One
 *
 * Banner Ads
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Banner
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */
/**
 * SDM_Banner_Block_Adminhtml_Slider_Grid class
 */
class SDM_Banner_Block_Adminhtml_Slider_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('sliderGrid');
        $this->setDefaultSort('slider_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Load collection for grid
     *
     * @return SDM_Banner_Model_Resource_Slider_Collection
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('slider/slider')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Define grid columns
     *
     * @return SDM_Banner_Block_Adminhtml_Slider_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('slider_id', array(
            'header'    => Mage::helper('slider')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'slider_id',
            ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('slider')->__('Title'),
            'align'     =>'left',
            'index'     => 'title',
            ));

        $this->addColumn('sliderimage', array(
            'header'    => Mage::helper('slider')->__('Image'),
            'align'     =>'left',
            'index'     => 'sliderimage',
            ));

        $this->addColumn('mobileimage', array(
            'header'    => Mage::helper('slider')->__('Image'),
            'align'     =>'left',
            'index'     => 'mobileimage',
            ));

        $this->addColumn('bannerurl', array(
            'header'    => Mage::helper('slider')->__('Banner Url'),
            'align'     =>'left',
            'index'     => 'bannerurl',
            ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('slider')->__('Status'),
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
                'header'    =>  Mage::helper('slider')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('slider')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                        )
                    ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
                ));

            // $this->addExportType('*/*/exportCsv', Mage::helper('slider')->__('CSV'));
            // $this->addExportType('*/*/exportXml', Mage::helper('slider')->__('XML'));

            return parent::_prepareColumns();
    }

    /**
     * Define mass actions
     *
     * @return SDM_Banner_Block_Adminhtml_Slider_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('slider_id');
        $this->getMassactionBlock()->setFormFieldName('slider');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => Mage::helper('slider')->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => Mage::helper('slider')->__('Are you sure?')
            ));

        $statuses = Mage::getSingleton('slider/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
            'label'=> Mage::helper('slider')->__('Change status'),
            'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('slider')->__('Status'),
                    'values' => $statuses
                    )
                )
            ));
        return $this;
    }

    /**
     * Get this row's url
     *
     * @param Mage_Core_Model_Abstract $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}
