<?php
/**
 * Mexbs_Tieredcoupon_Block_Adminhtml_Tieredcoupon_Grid
 * class that is used for displaying the grouping coupon grid
 *
 * @copyright MexBS
 * @author MexBS <it@mexbs.com>
 */

class Mexbs_Tieredcoupon_Block_Adminhtml_Tieredcoupon_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('tieredcoupon_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('tieredcoupon_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * prepares the grid collection
     *
     * @return Mexbs_Tieredcoupon_Block_Adminhtml_Tieredcoupon_Grid
     */
    protected function _prepareCollection()
    {
        /**
         * @var Mexbs_Tieredcoupon_Model_Resource_Tieredcoupon_Collection $collection
         */
        $collection = Mage::getModel('mexbs_tieredcoupon/tieredcoupon')->getCollection();
        $collection->addSubCouponsToSelect();

        // Map  ambiguous fields
        $collection->addFilterToMap('code', 'main_table.code');
        $collection->addFilterToMap('tieredcoupon_id', 'main_table.tieredcoupon_id');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepares the grid columns
     *
     * @return Mexbs_Tieredcoupon_Block_Adminhtml_Tieredcoupon_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('tieredcoupon_id', array(
            'header' => Mage::helper('mexbs_tieredcoupon')->__('ID'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'tieredcoupon_id',
        ));


        $this->addColumn('name', array(
            'header' => Mage::helper('mexbs_tieredcoupon')->__('Name'),
            'index' => 'name',
            'width' => '150px',
            'type'  => 'text',
            'sortable'  => false,
        ));

        $this->addColumn('code', array(
            'header' => Mage::helper('mexbs_tieredcoupon')->__('Code'),
            'index' => 'code',
            'width' => '150px',
            'type'  => 'text',
        ));

        $this->addColumn('sub_coupon_codes', array(
            'header' => Mage::helper('mexbs_tieredcoupon')->__('Sub Coupon Codes'),
            'index' => 'sub_coupon_codes',
            'width' => '150px',
            'type'  => 'text',
            'filter'=> false,
            'sortable'  => false,
        ));

        $this->addColumn('is_active', array(
            'header'    => Mage::helper('mexbs_tieredcoupon')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => array(
                1 => Mage::helper('mexbs_tieredcoupon')->__('Active'),
                0 => Mage::helper('mexbs_tieredcoupon')->__('Inactive')
            ),
        ));


        if (Mage::getSingleton('admin/session')->isAllowed('promo/tieredcoupon')) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('mexbs_tieredcoupon')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('mexbs_tieredcoupon')->__('Edit'),
                            'url'     => array('base'=>'*/*/edit'),
                            'field'   => 'tieredcoupon_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'is_system' => true,
                ));
        }
        return parent::_prepareColumns();
    }

    /**
     * gets the url of the row in the grid
     *
     * @param Mexbs_Tieredcoupon_Model_Tieredcoupon $row
     * @return bool|string
     */
    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('promo/tieredcoupon')) {
            return $this->getUrl('*/*/edit', array('tieredcoupon_id' => $row->getId(), 'back_button_compact' => true), false);
        }
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true), false);
    }
}
