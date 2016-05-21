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
 * SDM_Compatibility_Block_Adminhtml_Compatibility_Grid class
 */
class SDM_Compatibility_Block_Adminhtml_Compatibility_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize
     */
    public function __construct()
    {
        parent::__construct();

        // Set some defaults for our grid
        $this->setDefaultSort('die_productline');
        $this->setDefaultDir('ASC');
        $this->setId('compatibility_compatibility_grid');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Get class name
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'compatibility/compatibility_collection';
    }

    /**
     * Prepare collection
     *
     * @return SDM_Compatibility_Model_Resource_Compatibility_Collection
     */
    protected function _prepareCollection()
    {
        $resource = Mage::getSingleton('core/resource');
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $collection->getSelect()
            ->join(
                array('p1' => $resource->getTableName('compatibility/productline')),
                'main_table.die_productline_id = p1.productline_id',
                array('p1.name AS die_name')
            )
            ->join(
                array('p2' => $resource->getTableName('compatibility/productline')),
                'main_table.machine_productline_id = p2.productline_id',
                array('p2.name AS machine_name')
            );
        // $collection->setOrder('p1.name', 'ASC');
        // $collection->setOrder('position', 'ASC');   // additional sort
        // Mage::log($collection->getSelect()->__toString());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return void
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            array(
                'header'=> $this->__('ID'),
                'align' =>'right',
                'width' => '50px',
                'index' => 'id'
            )
        );
        $this->addColumn(
            'die_productline',
            array(
                'header'=> $this->__('Die Product Line'),
                'index' => 'die_name',
                'filter_index' => 'p1.name'
            )
        );
        $this->addColumn(
            'machine_productline',
            array(
                'header'=> $this->__('Machine Product Line'),
                'index' => 'machine_name',
                'filter_index' => 'p2.name'
            )
        );
        $this->addColumn(
            'associated_products',
            array(
                'header'=> $this->__('Associated SKUs'),
                'index' => 'associated_products'
            )
        );
        $this->addColumn(
            'position',
            array(
                'header'=> $this->__('Position'),
                'index' => 'position'
            )
        );
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
        // This is where our row data will link to
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}
