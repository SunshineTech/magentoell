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
 * SDM_Catalog_Block_Adminhtml_Catalog_Category_Edit_Tab_Product
 */
class SDM_Catalog_Block_Adminhtml_Catalog_Category_Tab_Product
    extends Mage_Adminhtml_Block_Catalog_Category_Tab_Product
{
    /**
     * Add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $parent = parent::_prepareColumns();

        $this->addColumn('attribute_set_id', array(
            'header'    => Mage::helper('catalog')->__('Attribute Set'),
            'width'     => 90,
            'index'     => 'attribute_set_id',
            'type'      => 'options',
            'options'   => $this->_getAttributeSetIdOptions()
        ));

        $this->addColumn('life_cycle', array(
            'header'    => Mage::helper('catalog')->__('Lifecycle'),
            'width'     => 90,
            'index'     => 'life_cycle',
            'type'      => 'options',
            'options'   => Mage::helper('sdm_catalog')->getLifecycleOptions()
        ));

        return $parent;
    }


    /**
     * User for various admin grids
     *
     * @return array
     */
    public function _getAttributeSetIdOptions()
    {
        $attributeSets = Mage::getResourceModel('eav/entity_attribute_set_collection')->load();
        
        $options = array();
        foreach ($attributeSets as $instance) {
            if ((int)$instance->getEntityTypeId() === Mage_Catalog_Model_Product::ENTITY) {
                $options[$instance->getId()] = $instance->getAttributeSetName();
            }
        }

        return $options;
    }
}
