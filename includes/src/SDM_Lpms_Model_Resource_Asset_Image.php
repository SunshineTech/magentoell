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
 * SDM_Lpms_Model_Resource_Asset_Image class
 */
class SDM_Lpms_Model_Resource_Asset_Image
    extends SDM_Lpms_Model_Resource_Abstract
{
    /**
     * Initialize table and PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('lpms/lpms_asset_image', 'entity_id');
        $this->_storeTableName = 'lpms/lpms_asset_image_store';
    }

    /**
     * Process store ids before deleting
     *
     * @param  Mage_Core_Model_Abstract $object
     * @return Mage_Lpms_Model_Resource_Asset
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        $url = $object->getData('image_url');
        if (!empty($url)) {
            unlink(Mage::getBaseDir('media') . $url);
        }

        return parent::_beforeDelete($object);
    }
}
