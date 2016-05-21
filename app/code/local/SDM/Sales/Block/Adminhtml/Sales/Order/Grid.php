<?php
/**
 * Separation Degrees Media
 *
 * Ellison's Mage_Sales customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Sales
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Sales_Block_Adminhtml_Sales_Order_View class
 */
class SDM_Sales_Block_Adminhtml_Sales_Order_Grid
    extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    /**
     * Prepare columns
     *
     * @return SDM_Sales_Block_Adminhtml_Sales_Order_Grid
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->removeColumn('base_grand_total');

        return $this;
    }
}
