<?php
/**
 * Separation Degrees Media
 *
 * This module handles all the store locator functionality for Ellison.
 *
 * The original code from this module is based off the FME_Gmapstrlocator module. We converted their
 * module to an SDM module rather than extending from it because the amount of modifications and
 * rewrites necessary for it to fit Ellison's spec were extensive, yet we still felt there was value
 * in using FME's module as a starting point.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Gmapstrlocator
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator_Grid class
 */
class SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('gmapstrlocatorGrid');
        $this->setDefaultSort('gmapstrlocator_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection
     *
     * @return SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('gmapstrlocator/location')->getCollection();
        $select = $collection->getSelect();
        $select->joinInner(
            array('gw' => 'gmapstrlocator_website'),
            'main_table.gmapstrlocator_id = gw.gmapstrlocator_id',
            'website_id'
        );
        $select->joinInner(
            array('cw' => 'core_website'),
            'cw.website_id = gw.website_id',
            ''
        );
        $select->group('main_table.gmapstrlocator_id');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('gmapstrlocator_id', array(
            'header'    => Mage::helper('gmapstrlocator')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            // Assciated website table does not have a primary key and `gmapstrlocator_id`
            // exist in in both main and website tables.
            'index'     => 'main_table.gmapstrlocator_id'
        ));

        $this->addColumn('store_name', array(
            'header'    => Mage::helper('gmapstrlocator')->__('Store Name'),
            'align'     =>'left',
            'index'     => 'store_name',
            'width'     => '350px'
        ));

        $this->addColumn('address', array(
            'header'    => Mage::helper('gmapstrlocator')->__('Address'),
            'align'     =>'left',
            'index'     => 'address',
            'width'     => '250px'
        ));

        $this->addColumn('city', array(
            'header'    => Mage::helper('gmapstrlocator')->__('City'),
            'align'     =>'left',
            'index'     => 'city'
        ));

        $this->addColumn('state', array(
            'header'    => Mage::helper('gmapstrlocator')->__('State'),
            'align'     =>'left',
            'index'     => 'state',
            'type'      => 'text'
        ));

        $this->addColumn('postal_code', array(
            'header'    => Mage::helper('gmapstrlocator')->__('Postal / Zip Code'),
            'align'     =>'left',
            'index'     => 'postal_code'
        ));

        $this->addColumn('country', array(
            'header'    => Mage::helper('gmapstrlocator')->__('Country'),
            'align'     =>'left',
            'index'     => 'country',
            'type'      => 'options',
            'options'   => Mage::getModel('gmapstrlocator/system_config_source_countrylist')->toOptionArray(true)
        ));

        $this->addColumn('has_design', array(
            'header'    => Mage::helper('gmapstrlocator')->__('Design Center'),
            'align'     =>'left',
            'index'     => 'has_design_center',
            'type'      => 'options',
            'options'   => Mage::getModel('gmapstrlocator/system_config_source_yesno')->toOptionArray(true)
        ));

        $this->addColumn('store_type',
            array(
                'header'    => Mage::helper('gmapstrlocator')->__('Store Type'),
                'align'     =>'left',
                'index'     => 'store_type',
                'type'      => 'options',
                'options'   => SDM_Gmapstrlocator_Model_System_Config_Source_Storetypes::toOptionArray(),
                'renderer'  => 'SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator_Renderer_Store',
                'filter_condition_callback' => array($this, '_filterStoreTypes'),    // For filtering
            )
        );

        $this->addColumn('agent_type', array(
            'header'    => Mage::helper('gmapstrlocator')->__('Agent Type'),
            'align'     =>'left',
            'index'     => 'agent_type'
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('gmapstrlocator')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(
                1 => 'Enabled',
                2 => 'Disabled',
            )
        ));

        $this->addColumn('assigned_websites',
            array(
                'header'    => Mage::helper('gmapstrlocator')->__('Websites'),
                'align'     =>'left',
                'index'     => 'assigned_websites',
                'type'      => 'options',
                'options'   => Mage::helper('sdm_core')->getAssociativeEllisonSystemCodes(true),
                'renderer'  => 'SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator_Renderer_Website',
                'filter_condition_callback' => array($this, '_filterWebsiteIds'),    // For filtering
            )
        );

        $this->addColumn('action', array(
            'header'    =>  Mage::helper('gmapstrlocator')->__('Action'),
            'width'     => '100',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => array(array(
                'caption'   => Mage::helper('gmapstrlocator')->__('Edit'),
                'url'       => array('base'=> '*/*/edit'),
                'field'     => 'id'
            )),
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'stores',
            'is_system' => true
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('gmapstrlocator')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('gmapstrlocator')->__('XML'));

        return parent::_prepareColumns();
    }

    /**
     * Prepare mass actions
     *
     * @return SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('gmapstrlocator_id_main');
        $this->getMassactionBlock()->setFormFieldName('gmapstrlocator');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => Mage::helper('gmapstrlocator')->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => Mage::helper('gmapstrlocator')->__('Are you sure?')
            ));

        $statuses = Mage::getSingleton('gmapstrlocator/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
            'label'=> Mage::helper('gmapstrlocator')->__('Change status'),
            'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('gmapstrlocator')->__('Status'),
                    'values' => $statuses
                )
            )
        ));
        return $this;
    }

    /**
     * Get row url
     *
     * @param Mage_Core_Model_Abstract $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
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
        if (!$websiteId = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->getSelect()
            ->join(
                array('w' => $collection->getTable('gmapstrlocator/gmapstrlocator_website')),
                'main_table.gmapstrlocator_id = w.gmapstrlocator_id',
                array('gmapstrlocator_id')
            )
            ->where('w.website_id = ?', $websiteId);
    }

    /**
     * Allows the store types to be filtered correctly
     *
     * @param SDM_Gmapstrlocator_Model_Resource_Item_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column           $column
     *
     * @return void
     */
    protected function _filterStoreTypes($collection, $column)
    {
        if (!$storeType = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->getSelect()
            ->where("main_table.store_type LIKE '%$storeType%'");
    }
}
