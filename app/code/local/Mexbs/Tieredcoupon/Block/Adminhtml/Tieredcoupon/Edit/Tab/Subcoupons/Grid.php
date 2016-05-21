<?php
/**
 * Mexbs_Tieredcoupon_Block_Adminhtml_Tieredcoupon_Edit_Tab_Subcoupons_Grid
 * class that is used for displaying the grid of the sub coupons
 *
 * @copyright MexBS
 * @author MexBS <it@mexbs.com>
 */
class Mexbs_Tieredcoupon_Block_Adminhtml_Tieredcoupon_Edit_Tab_Subcoupons_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * gets the current grouping coupon
     *
     * @return null|Mexbs_Tieredcoupon_Model_Tieredcoupon
     */
    protected function _getTieredCoupon()
    {
        return Mage::registry('current_tieredcoupon');
    }

    /**
     * constructor, sets default values to the grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('tieredcoupon_edit_tab_subcoupons_grid');
        $this->setVarNameFilter('tieredcoupon_subcoupons_filter');
        $this->setUseAjax(true);
        if ($this->_getTieredCoupon() && $this->_getTieredCoupon()->getId()) {
            $this->setDefaultFilter(array('in_subcoupons'=>1));
        }
    }

    /**
     * filters the sub coupons collection according the 'in_subcoupons' filter
     *
     * @param Varien_Object $column
     * @return Mexbs_Tieredcoupon_Block_Adminhtml_Tieredcoupon_Edit_Tab_Subcoupons_Grid
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_subcoupons') {
            $subCouponCodes = $this->getSelectedSubCoupons();
            if (empty($subCouponCodes)) {
                $subCouponCodes = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('code', array('in'=>$subCouponCodes));
            } else {
                if($subCouponCodes) {
                    $this->getCollection()->addFieldToFilter('code', array('nin'=>$subCouponCodes));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * prepares the collection for grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        /**
         * @var Mage_SalesRule_Model_Resource_Coupon_Collection $collection
         */
        $collection = Mage::getResourceModel('salesrule/coupon_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * gets the selected sub coupon codes
     *
     * @return array
     */
    public function getSelectedSubCoupons()
    {
        $selectedSubCoupons = $this->getSelectedSubcouponsInGrid();
        if (is_null($selectedSubCoupons)) {
            $selectedSubCoupons = ($this->_getTieredCoupon() ? $this->_getTieredCoupon()->getSubCouponCodes() : array());
        }
        return $selectedSubCoupons;
    }

    /**
     * prepares grid columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_subcoupons', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_subcoupons',
            'values'    => $this->getSelectedSubCoupons(),
            'align'     => 'center',
            'index'     => 'code',
            'use_index' => true
        ));
        $this->addColumn('code', array(
            'header' => Mage::helper('mexbs_tieredcoupon')->__('Coupon Code'),
            'index'  => 'code'
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('mexbs_tieredcoupon')->__('Created On'),
            'index'  => 'created_at',
            'type'   => 'datetime',
            'align'  => 'center',
            'width'  => '160'
        ));

        return parent::_prepareColumns();
    }

    /**
     * gets grid url (used for ajax)
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/subcouponsgrid', array('_current'=> true));
    }
}


