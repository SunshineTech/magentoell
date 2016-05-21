<?php
/**
 * Separation Degrees Media
 *
 * Magento catalog customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Catalog
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Catalog_Block_Adminhtml_Cache_Additional class
 */
class SDM_Catalog_Block_Adminhtml_Cache_Additional
    extends Mage_Adminhtml_Block_Cache_Additional
{
    /**
     * Clean catalog images url
     *
     * @return string
     */
    public function getCleanCatalogImagesUrl()
    {
        return $this->getUrl('*/*/cleanCatalogImages');
    }

    /**
     * Clean resized images url
     *
     * @return string
     */
    public function getCleanResizedImagesUrl()
    {
        return $this->getUrl('*/*/cleanResizedImages');
    }
}
