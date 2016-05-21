<?php
/**
 * Separation Degrees One
 *
 * Implements the customer/retailer discount logic for viewing and obtaining
 * discounts and prices, as wel as managing the customer groups.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CustomerDiscount
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_CustomerDiscount_Block_Adminhtml_Discountgroup_Grid class
 */
class SDM_CustomerDiscount_Block_Adminhtml_Discountgroup_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize
     */
    public function __construct()
    {
        parent::__construct();

        // $this->setDefaultSort('category_id');
        $this->setId('customer_discount_group_grid');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Get class name
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'customerdiscount/discountgroup_collection';
    }

    /**
     * Prepoare collection
     *
     * @return SDM_CustomerDiscount_Block_Adminhtml_Discountgroup_Grid
     */
    protected function _prepareCollection()
    {
        $resource = Mage::getSingleton('core/resource');
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $collection->getSelect()
            ->join(
                array('cg' => $resource->getTableName('customer/customer_group')),
                'main_table.customer_group_id = cg.customer_group_id',
                array('cg.customer_group_code AS customer_group_name')
            )
            ->join(
                array('dc' => $resource->getTableName('taxonomy/item')),
                'main_table.category_id = dc.entity_id',
                array('dc.name AS discount_category_name')
            );
        // Below prevents column sorting in admin
        // $collection->setOrder('main_table.customer_group_id', 'ASC');
        // $collection->setOrder('dc.position', 'ASC');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return SDM_CustomerDiscount_Block_Adminhtml_Discountgroup_Grid
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
            'customer_group_id',
            array(
                'header'=> $this->__('Customer Group'),
                'index' => 'customer_group_name',
                'filter_index' => 'cg.customer_group_code'
            )
        );
        $this->addColumn(
            'category_id',
            array(
                'header'=> $this->__('Discount Category (From Taxonomy)'),
                'index' => 'discount_category_name',
                'filter_index' => 'dc.name',
                'type' => 'options',
                'options' => Mage::helper('customerdiscount')->getAllDiscountCategoryOptions(),
                'filter_condition_callback' => array($this, '_filterDiscountCategories'),
            )
        );
        $this->addColumn(
            'amount',
            array(
                'header'=> $this->__('Discount Amount (%)'),
                'index' => 'amount'
            )
        );
        $this->addColumn(
            'action',
            array(
                'header' => $this->__('Action'),
                'align' => 'center',
                'width' => '100px',
                'type' => 'action',
                'getter'    => 'getId',
                'actions' => array(
                    array(
                        'caption' => $this->__('Edit'),
                        'url' => array(
                            'base' => '*/*/edit',
                        ),
                        'field' => 'id'
                    ),
                    array(
                        'caption' => $this->__('Delete'),
                        'url' => array(
                            'base' => '*/*/delete',
                        ),
                        'field' => 'id'
                    ),
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'entity_id',
                'renderer'  => 'customerdiscount/adminhtml_discountgroup_grid_renderer_actions',
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
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * Apply filter for the options column
     *
     * @param SDM_CustomerDiscount_Model_Resource_Discountgroup_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column                      $column
     *
     * @return array
     */
    protected function _filterDiscountCategories($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addFieldToFilter('main_table.category_id', $value);
    }
}
