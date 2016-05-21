<?php
/**
 * Separation Degrees Media
 *
 * Ellison's custom Landing Page Management System (LPMS).
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Lpms
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Lpms_Helper_Data class
 */
class SDM_Lpms_Helper_Data
    extends Mage_Core_Helper_Abstract
{
    /**
     * Get types
     *
     * @return array
     */
    public function getPageTypes()
    {
        return array(
            'page'          => "CMS Page",
            'news'          => "News Article",
            'press'         => "Press Releases",
            'designer'      => "Designer Article"
        );
    }

    /**
     * Get items
     *
     * @return array
     */
    public function getTaxonomyItemsForCmsPage()
    {
        $taxonomyItems = array('' => ' -- Select A Designer --');
        // Filter designers
        $collection = Mage::getModel('taxonomy/item')
            ->getCollection()
            ->filterType('designer');
        foreach ($collection as $item) {
            $taxonomyItems[$item->getId()] = $item->getName();
        }
        return $taxonomyItems;
    }

    /**
     * Delete assets
     *
     * @param integer $pageId
     *
     * @return void
     */
    public function deleteAllPageAssets($pageId)
    {
        $resource = Mage::getResourceModel('lpms/asset');
        $assets = $resource->allExistingAssetIdsByPage($pageId);
        $resource->deleteAssets($assets);
    }

    /**
     * Recieves a multidimensional array of asset data, along with a CMS page ID,
     * and saves everything to the database. If asset IDs are provided, then the
     * existing assets will be updated.
     *
     * @param  array    $data
     * @param  int|null $pageId
     * @return $this
     */
    public function saveAssetData($data, $pageId = null)
    {
        $resource = Mage::getResourceModel('lpms/asset');
        $existingAssets = $resource->allExistingAssetIdsByPage($pageId);
        $existingAssetsSaved = array();

        $existingAssetImages = $resource->allExistingAssetImageIdsByPage($pageId);
        $existingAssetImagesSaved = array();

        $order = 1;
        foreach ($data as $assetData) {
            $asset = Mage::getModel('lpms/asset')
                ->initWithData($assetData)
                ->setData('sort_order', $order++);

            if ($pageId) {
                $asset->setCmsPageId($pageId);
                if ($asset->getId()) {
                    $existingAssetsSaved[] = $asset->getId();
                }
            }

            $asset->save();

            $assetImageDataArray = isset($assetData['asset_images']) ? $assetData['asset_images'] : array();
            $assetImageOrder = 1;
            foreach ($assetImageDataArray as $assetImageData) {
                $assetImage = Mage::getModel('lpms/asset_image')
                    ->initWithData($assetImageData, $pageId)
                    ->setData('cms_asset_id', $asset->getId())
                    ->setData('sort_order', $assetImageOrder++);

                // Did the image save correctly?
                if ($assetImage->getData('image_url') == null || strlen($assetImage->getData('image_url')) < 0) {
                    continue;
                }

                if ($pageId) {
                    $assetImage->setCmsPageId($pageId);
                    if ($assetImage->getId()) {
                        $existingAssetImagesSaved[] = $assetImage->getId();
                    }
                }
                $assetImage->save();
            }
            
        }

        // Delete any remaining assets
        $deletedAssets = array_diff($existingAssets, $existingAssetsSaved);
        if ($deletedAssets) {
            $resource->deleteAssets($deletedAssets);
        }

        // Delete any remaining asset images
        $deletedAssetImages = array_diff($existingAssetImages, $existingAssetImagesSaved);
        if ($deletedAssetImages) {
            $resource->deleteAssetImages($deletedAssetImages);
        }

        return $this;
    }

    /**
     * Grabs all the assets for a page and returns all their data as JSON
     *
     * @param  int $pageId
     * @return string
     */
    public function getPageAssetsAsJson($pageId)
    {
        /**
         * Get the assets for a page, loop through each one, get the data as an array,
         * lump them all into a big array, and then return it
         */
        $collection = Mage::getModel('lpms/asset')
            ->getCollection()
            ->filterByPageId($pageId)
            ->sortAssets();

        $assetData = array();
        foreach ($collection as $asset) {
            $assetData[] = $asset->getDataForFrontend();
        }

        return json_encode($assetData);
    }
}
