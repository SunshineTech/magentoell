<?php
/**
 * Separation Degrees One
 *
 * Ellison's Mage_Customer customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Customer
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Customer_Block_Adminhtml_Customer_Group_Grid class
 */
class SDM_Customer_Block_Adminhtml_Customer_Group_Grid
    extends Mage_Adminhtml_Block_Customer_Group_Grid
{
    /**
     * Configuration of grid. Adds the custom columns.
     *
     * @return SDM_Customer_Block_Adminhtml_Customer_Group_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumnAfter(
            'position',
            array(
                'header' => Mage::helper('sdm_customer')->__('Position'),
                'index' => 'position',
                'width' => '50px'
            ),
            'class_name'
        );
        $this->addColumnAfter(
            'min_qty_override',
            array(
                'header' => Mage::helper('sdm_customer')->__('Min. Qty. Override'),
                'index' => 'min_qty_override',
                'width' => '100px'
            ),
            'position'
        );

        return parent::_prepareColumns();
    }
}
