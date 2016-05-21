<?php
/**
 * Separation Degrees Media
 *
 * Implements the product compatibility functionality.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Compatibility
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Compatibility_Block_Adminhtml_Productline_Grid class
 */
class SDM_Compatibility_Block_Adminhtml_Productline_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize
     */
    public function __construct()
    {
        parent::__construct();

        // Set some defaults for our grid
        $this->setDefaultSort('name');
        $this->setId('compatibility_productline_grid');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Get class name
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'compatibility/productline_collection';
    }

    /**
     * Prepare collection
     *
     * @return SDM_Compatibility_Model_Resource_Productline_Collection
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return SDM_Compatibility_Block_Adminhtml_Productline_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            array(
                'header'=> $this->__('ID'),
                'align' =>'right',
                'width' => '50px',
                'index' => 'productline_id'
            )
        );
        $this->addColumn(
            'name',
            array(
                'header'=> $this->__('Product Line'),
                'index' => 'name'
            )
        );
        $this->addColumn(
            'type',
            array(
                'header'=> $this->__('Type'),
                'index' => 'type',
                'type' => 'options',
                'options' => Mage::helper('compatibility')->getProductTypeArray(),
            )
        );
        $this->addColumn(
            'website_ids',
            array(
                'header'=> $this->__('Websites'),
                'index' => 'website_ids',
                'type' => 'options',
                'options' => Mage::helper('sdm_core')->getAssociativeEllisonSystemCodes(),
                'renderer'  => 'SDM_Compatibility_Block_Adminhtml_Renderer_Website',
                'filter_condition_callback' => array($this, '_filterWebsiteIds'),    // For filtering
            )
        );
        $this->addColumn(
            'code',
            array(
                'header'=> $this->__('Code'),
                'index' => 'code'
            )
        );
        $this->addColumn(
            'action',
            array(
                'header' => $this->_getHelper()->__('Action'),
                'width' => '50px',
                'type' => 'action',
                'getter'    => 'getId',
                'actions' => array(
                    array(
                        'caption' => $this->_getHelper()->__('Edit'),
                        'url' => array(
                            'base' => '*/*/pedit',
                        ),
                        'field' => 'id'
                    ),
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'entity_id',
            )
        );

        return parent::_prepareColumns();
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
        return $this->getUrl('*/*/pedit', array('id' => $row->getId()));
    }

    /**
     * Allows the website IDs to be filtered correctly in the grid
     *
     * @param SDM_Compatibility_Model_Resource_Compatibility_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column                   $column
     *
     * @return void
     */
    protected function _filterWebsiteIds($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addFieldToFilter('website_ids', array('finset' => $value));
    }

    /**
     * Get helper
     *
     * @return SDM_Taxonomy_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('taxonomy');
    }
}
