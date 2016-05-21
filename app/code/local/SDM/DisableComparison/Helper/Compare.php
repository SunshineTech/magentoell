<?php
/**
 * Separation Degrees Media
 *
 * Disable Product/Project Comparison
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_DisableComparison
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_DisableComparison_Helper_Compare class
 */
class SDM_DisableComparison_Helper_Compare
    extends Mage_Catalog_Helper_Product_Compare
{
    /**
     * Disabled url for adding product to conpare list
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return empty
     */
    public function getAddUrl($product)
    {
        return '';
    }
}
