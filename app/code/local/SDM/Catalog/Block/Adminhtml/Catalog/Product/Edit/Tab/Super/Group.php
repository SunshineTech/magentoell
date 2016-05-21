<?php
/**
 * Related products admin grid
 *
 * PHP Version 5
 *
 * @category Mage
 * @package  Mage_Adminhtml
 * @author   Magento Core Team <core@magentocommerce.com>
 */

/**
 * SDM_Catalog_Block_Adminhtml_Catalog_Product_Edit_Tab_Super_Group
 */
class SDM_Catalog_Block_Adminhtml_Catalog_Product_Edit_Tab_Super_Group
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Group
{
    /**
     * Add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $parent = parent::_prepareColumns();

        $this->addColumn('life_cycle', array(
            'header'    => Mage::helper('catalog')->__('Lifecycle'),
            'width'     => 90,
            'index'     => 'life_cycle',
            'type'      => 'options',
            'options'   => Mage::helper('sdm_catalog')->getLifecycleOptions()
        ));

        return $parent;
    }
}
