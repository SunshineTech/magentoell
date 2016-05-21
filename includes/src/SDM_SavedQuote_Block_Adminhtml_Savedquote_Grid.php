<?php
/**
 * Separation Degrees Media
 *
 * Allows saving quotes that can be later be converted into orders with preserved
 * pricing.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_SavedQuote
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * Saved quote grid rendered in admin
 */
class SDM_SavedQuote_Block_Adminhtml_Savedquote_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize grid
     */
    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSort('created_at');
        $this->setId('savedquote_list');   // ID of the grid div
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Load saved quotes for grid
     *
     * @return SDM_SavedQuote_Model_Resource_Savedquote_Collection
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass())
            ->addFieldToFilter(
                'is_active',
                array('neq' => SDM_SavedQuote_Helper_Data::PENDING_FLAG)
            );
        $collection->getSelect()->joinLeft(
            array('addr' => $collection->getResource()->getTable('savedquote/savedquote_address')),
            'main_table.entity_id = addr.saved_quote_id AND addr.address_type = "shipping"',
            array('company' => 'addr.company')
        );
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Collection identifier
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'savedquote/savedquote_collection';
    }

    /**
     * Configure columns for grid
     *
     * @return SDM_SavedQuote_Block_Adminhtml_Savedquote_Grid
     */
    protected function _prepareColumns()
    {
        $statuses = Mage::helper('savedquote')->getStatusOptions();

        // Add the columns that should appear in the grid
        $this->addColumn(
            'quote_id',
            array(
                'header'=> Mage::helper('savedquote')->__('Saved Quote #'),
                'width' => '80px',
                'type'  => 'text',
                'index' => 'increment_id',
            )
        );
        $this->addColumn(
            'created_at',
            array(
                'header' => Mage::helper('savedquote')->__('Created On'),
                'index' => 'created_at',
                'type' => 'datetime',
                'width' => '170px',
            )
        );
        $this->addColumn(
            'expires_at',
            array(
                'header' => Mage::helper('savedquote')->__('Expires On'),
                'index' => 'expires_at',
                'type' => 'datetime',
                'width' => '170px',
            )
        );
        $this->addColumn(
            'customer_email',
            array(
                'header' => Mage::helper('savedquote')->__('Email'),
                'index' => 'customer_email',
                'width' => '200px',
                'type'  => 'text',
            )
        );
        $this->addColumn(
            'name',
            array(
                'header' => Mage::helper('savedquote')->__('Name'),
                'index' => 'name',
                'type' => 'text',
            )
        );
        $this->addColumn(
            'company',
            array(
                'header' => Mage::helper('savedquote')->__('Company'),
                'index' => 'company',
                'type' => 'text',
            )
        );
        $this->addColumn(
            'status',
            array(
                'header' => Mage::helper('savedquote')->__('Status'),
                'index' => 'is_active',
                'type' => 'text',
                'width' => '150px',
                'type' => 'options',
                'options' => $statuses
            )
        );
        $this->addColumn(
            'available',
            array(
                'header'   => Mage::helper('savedquote')->__('Order?'),
                'renderer' => 'savedquote/adminhtml_savedquote_grid_renderer_available',
                'type'     => 'text',
                'filter'   => false,
                'sortable' => false,
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * Returns the URL to view/edit the record
     *
     * @param SDM_SavedQuote_Model_Savedquote $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        // This is where our row data will point to
        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }
}
