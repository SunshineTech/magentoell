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
 * SDM_Lpms_Model_Asset class
 */
class SDM_Lpms_Model_Asset extends SDM_Lpms_Model_Abstract
{
    /**
     * Holds a collection or array of asset images
     *
     * @var null
     */
    protected $_assetImages = null;

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('lpms/asset');
    }

    /**
     * Returns an array of each LPMS Asset with it's code (key), name, and fields used
     *
     * @return array
     */
    public function getTypes()
    {
        return array(
            'freeform'      => $this->_getHelper()->__('Freeform'),
            'search'        => $this->_getHelper()->__('Search'),
            'image'         => $this->_getHelper()->__('Images'),
            'products'      => $this->_getHelper()->__('Products')
        );
    }

    /**
     * Get the field names submitted through the frontend
     *
     * @return array
     */
    public function getFrontendFields()
    {
        return array(
            'id',
            'type',
            'name',
            'start_date',
            'end_date',
            'is_active',
            'image_format',
            'content',
            'week_days',
            'cms_page_id',
            'store_ids',
            'asset_images'
        );
    }

    /**
     * Makes sure we save a valid type value
     *
     * @param string $type
     *
     * @return SDM_Lpms_Model_Asset
     */
    public function setType($type)
    {
        $types = array_keys($this->getTypes());
        $type = in_array($type, $types) ? $type : reset($types);
        $this->setData('type', $type);
        return $this;
    }

    /**
     * Gets a new lpms asset block
     *
     * @return $block
     */
    public function getBlock()
    {
        $block = Mage::app()->getLayout()
            ->createBlock(
                'SDM_Lpms_Block_Asset',
                'asset-'.$this->getId(),
                array('template' => $this->getTemplate())
            )->setAsset($this);
        return $block;
    }

    /**
     * Gets the current template for this asset
     *
     * @return string
     */
    public function getTemplate()
    {
        $template = $this->getType() ? $this->getType() : 'freeform';
        return 'sdm/lpms/asset/'.$template.'.phtml';
    }

    /**
     * Sets content to asset and returns html
     *
     * @return string
     */
    public function renderAsset()
    {
        if ($this->canShowAsset()) {
            return $this->getBlock()
                ->setData('content', $this->getData('content'))
                ->toHtml();
        }
        return '';
    }

    /**
     * Returns a collection of asset images based off the asset ID
     *
     * @return Varien_Data_Collection
     */
    public function getAssetImages()
    {
        if ($this->_assetImages === null) {
            $this->_assetImages = Mage::getModel('lpms/asset_image')
                ->getCollection()
                ->filterByAssetId($this->getId())
                ->sortAssetImages();
        }
        return $this->_assetImages;
    }

    /**
     * Gets all the asset images that can be shown based off visibility filters
     *
     * @return array
     */
    public function getVisibleAssetImages()
    {
        $visibleAssetImages = array();
        foreach ($this->getAssetImages() as $assetImage) {
            if ($assetImage->canShowAsset()) {
                $visibleAssetImages[] = $assetImage;
            }
        }
        return $visibleAssetImages;
    }

    /**
     * Gets the product collection carousel HTML
     *
     * @return string
     */
    public function getCollectionCarouselHtml()
    {
        if ($this->getType() !== 'products') {
            return '';
        }

        return Mage::helper('rendercollection')
            ->initNewCarousel()
            ->setSkus($this->getData('content'))
            ->toHtml();
    }

    /**
     * Gets the search carousel HTML
     *
     * @return string
     */
    public function getSearchCarouselHtml()
    {
        if ($this->getType() !== 'search') {
            return '';
        }

        return Mage::helper('rendercollection')
            ->initNewCarousel()
            ->setSearchString($this->getData('content'))
            ->toHtml();
    }

    /**
     * Returns the search url, used for "View All"
     *
     * @return string
     */
    public function getSearchUrl()
    {
        $page = Mage::getSingleton('cms/page');
        if ($page->getId() && $page->getIdentifier() === 'print-catalogs') {
            // Don't show Search URL for print catalogs
            return "";
        } else {
            // Parse out the chunk of the URL we're interested in
            $searchString = $this->getData('content');
            $searchString = explode('#', $searchString);
            $searchString = reset($searchString);
            $searchString = explode('?', $searchString);
            $searchString = end($searchString);
            return "/catalog" . (strpos($searchString, '=') !== false ? '?'.$searchString : '');
        }
    }
}
