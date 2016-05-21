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
 * SDM_FileUpload_Model_Resource_File_Collection class
 */
class SDM_FileUpload_Model_Resource_File_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
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
}
