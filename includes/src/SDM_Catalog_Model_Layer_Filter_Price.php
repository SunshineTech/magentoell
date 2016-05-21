<?php
/**
 * Separation Degrees One
 *
 * Magento catalog customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Catalog
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Catalog_Model_Layer_Filter_Price class
 */
class SDM_Catalog_Model_Layer_Filter_Price
    extends AdjustWare_Nav_Model_Catalog_Layer_Filter_Price
{
    /**
     * Remove filters that have no count
     *
     * @return Mage_Catalog_Model_Layer_Filter_Abstract
     */
    protected function _initItems()
    {
        parent::_initItems();

        foreach ($this->_items as $key => $item) {
            if ($item->getCount() <= 0) {
                unset($this->_items[$key]);
            }
        }

        return $this->_items;
    }
}
