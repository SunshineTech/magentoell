<?php
/**
 * Separation Degrees One
 *
 * Valutec Giftcard Integration
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Valutec
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Valutec_Model_Adminhtml_System_Config_Source_Product_Type class
 */
class SDM_Valutec_Model_Adminhtml_System_Config_Source_Product_Type
{
    /**
     * List all product types
     *
     * @return array
     */
    public function toOptionArray()
    {
        return Mage::getSingleton('eav/config')
            ->getAttribute('catalog_product', 'product_type')
            ->getSource()
            ->getAllOptions(false);
    }
}
