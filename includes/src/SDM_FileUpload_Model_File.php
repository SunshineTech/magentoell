<?php
/**
 * Separation Degrees Media
 *
 * Extension to upload file
 *
 * PHP Version 5.5
 *
 * @category  SDM
 * @package   SDM_FileUpload
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_FileUpload_Model_File class
 */
class SDM_FileUpload_Model_File extends Mage_Core_Model_Abstract
{
    /**
     * Initialize
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sdm_upload/file');
    }

    /**
     * Load the record using the composite key
     *
     * @param int $parentId
     * @param str $type
     *
     * @return SDM_FileUpload_Model_File
     */
    public function loadByKey($parentId, $type)
    {
        $upload = $this->getCollection()->addFieldToFilter('parent_id', $parentId)
            ->addFieldToFilter('type', $type)
            ->getFirstItem();

        return $upload;
    }
}
