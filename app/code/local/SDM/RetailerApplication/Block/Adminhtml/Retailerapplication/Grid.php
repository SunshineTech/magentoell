<?php
/**
 * Separation Degrees One
 *
 * Manages the retailer application
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_RetailerApplication
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_RetailerApplication_Block_Adminhtml_Retailerapplication_Grid class
 */
class SDM_RetailerApplication_Block_Adminhtml_Retailerapplication_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize
     */
    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSort('updated_at');
        $this->setId('retailerapplication_id');   // ID of the grid div
        $this->setDefaultDir('DESC');
        $this->setDefaultFilter(array('status' => SDM_RetailerApplication_Helper_Data::STATUS_UNDER_REVIEW));
        $this->setSaveParametersInSession(true);
    }

    /**
     * Sets up the collection we want and joins it to the customer table
     *
     * @return $this
     */
    protected function _prepareCollection()
    {

        $prefix = Mage::getConfig()->getTablePrefix();
        $resource = Mage::getSingleton('core/resource');

        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $collection
            ->getSelect()
            ->join(
                $prefix.$resource->getTableName("customer/entity"),
                'main_table.customer_id = '.$prefix.$resource->getTableName("customer/entity").'.entity_id',
                array('email AS customer_email')
            );

        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'main_table.customer_id AS customer_id',
                'main_table.company_name AS company_name',
                'customer_entity.email AS email',
                'main_table.status AS status',
                'main_table.created_at AS created_at',
                'main_table.updated_at AS updated_at'
            ));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Returns the class we are using for this collection
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'retailerapplication/application_collection';
    }

    /**
     * Prepares columns for this grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $status = array(
            SDM_SavedQuote_Helper_Data::ACTIVE_FLAG => 'Active',
            SDM_SavedQuote_Helper_Data::INACTIVE_FLAG => 'Converted'
        );

        $this->addColumn(
            'customer_id',
            array(
                'header'=> Mage::helper('savedquote')->__('Customer ID'),
                'width' => '80px',
                'type'  => 'text',
                'index' => 'customer_id',
            )
        );

        $this->addColumn(
            'company_name',
            array(
                'header'=> Mage::helper('savedquote')->__('Company Name'),
                'width' => '80px',
                'type'  => 'text',
                'index' => 'company_name',
            )
        );

        $this->addColumn(
            'email',
            array(
                'header'=> Mage::helper('savedquote')->__('Customer Email'),
                'width' => '80px',
                'type'  => 'text',
                'index' => 'email',
            )
        );

        $this->addColumn(
            'status',
            array(
                'header' => Mage::helper('savedquote')->__('Status'),
                'index' => 'status',
                'type' => 'text',
                // 'width' => '100px',
                'type' => 'options',
                'options' => Mage::helper('retailerapplication')->getStatuses()
            )
        );

        $this->addColumn(
            'created_at',
            array(
                'header' => Mage::helper('savedquote')->__('Created At'),
                'index' => 'created_at',
                'type' => 'datetime'
            )
        );

        $this->addColumn(
            'updated_at',
            array(
                'header' => Mage::helper('savedquote')->__('Last Updated'),
                'index' => 'updated_at',
                'type' => 'datetime'
            )
        );

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('customer')->__('Action'),
                // 'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getCustomerId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('customer')->__('Edit Customer'),
                        'url'       => array('base'=> '*/customer/edit'),
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
     * Returns the URL to view/edit the record
     *
     * @param Mage_Core_Model_Abstract $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        // This is where our row data will point to
        return $this->getUrl('*/customer/edit', array('id' => $row->getCustomerId()));
    }
}
