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
 * SDM_Lpms_Model_Resource_Asset_Image_Collection class
 */
class SDM_Lpms_Model_Resource_Asset_Image_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('lpms/asset_image');
    }

    /**
     * Filter the image collection by asset id
     *
     * @param  int $assetId
     * @return $this
     */
    public function filterByAssetId($assetId)
    {
        $this->addFieldToFilter('cms_asset_id', array('eq' => $assetId));
        return $this;
    }

    /**
     * Filter image assets collection by page ID
     *
     * @param  int $pageId
     * @return $this
     */
    public function filterByPageId($pageId)
    {
        $this->addFieldToFilter('cms_page_id', array('eq' => $pageId));
        return $this;
    }

    /**
     * Sorts asset images by their sort order
     *
     * @return $this
     */
    public function sortAssetImages()
    {
        $this->setOrder('sort_order', 'ASC');
        return $this;
    }
}
