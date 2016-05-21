<?php
/**
 * Separation Degrees Media
 *
 * Product Carousel widget
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_RenderCollection
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_RenderCollection_Helper_Data class
 */
class SDM_RenderCollection_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Initializes a new collection carousel
     *
     * @param  Varien_Data_Collection|null $collection
     * @param  string                      $type
     * @return SDM_RenderCollection_Block_Carousel
     */
    public function initNewCarousel($collection = null, $type = 'product')
    {
        $block = Mage::app()
            ->getLayout()
            ->createBlock('rendercollection/carousel')
            ->setCollection($collection, $type);

        return $block;
    }

    /**
     * Initializes a new collection listing
     *
     * @param  Varien_Data_Collection|null $collection
     * @param  string                      $type
     * @param  boolean|Varient_object      $toolbarOptions
     * @return SDM_RenderCollection_Block_Listing
     */
    public function initNewListing($collection = null, $type = 'product', $toolbarOptions = false)
    {
        $block = Mage::app()
            ->getLayout()
            ->createBlock('rendercollection/listing')
            ->setCollection($collection, $type);
        if ($toolbarOptions) {
            $block->setToolbarOptions($toolbarOptions);
        }

        return $block;
    }
}
