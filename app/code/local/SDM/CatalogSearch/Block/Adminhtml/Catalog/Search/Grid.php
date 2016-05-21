<?php
/**
 * Separation Degrees One
 *
 * Magento catalog search customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CatalogSearch
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Adminhtml_Block_Catalog_Search_Grid class
 */
class SDM_CatalogSearch_Block_Adminhtml_Catalog_Search_Grid extends Mage_Adminhtml_Block_Catalog_Search_Grid
{
    /**
     * Prepare Grid columns
     *
     * @return Mage_Adminhtml_Block_Catalog_Search_Grid
     */
    protected function _prepareColumns()
    {
        $return = parent::_prepareColumns();

        $this->addColumnAfter('type', array(
            'header'    => Mage::helper('catalog')->__('Type'),
            'align'     => 'left',
            'index'     => 'type',
            'style'     => '50px'
        ), 'action');

        return $return;
    }
}
