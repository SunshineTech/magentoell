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
 * SDM_CustomerDiscount_Block_Adminhtml_Discountgroup_Matrix class
 */
class SDM_CustomerDiscount_Block_Adminhtml_Discountgroup_Matrix
    extends Mage_Core_Block_Template
{
    /**
     * Uses the helper function to get the matrix data
     *
     * @return array
     */
    public function getMatrix()
    {
        return Mage::helper('customerdiscount')->getMatrix();
    }
}
