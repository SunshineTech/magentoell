<?php
/**
 * Separation Degrees Media
 *
 * Extension to upload file
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_FileUpload
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * Helper for the SDM file upload extension
 */
class SDM_FileUpload_Helper_Data extends SDM_Core_Helper_Data
{
    /**
     * Core log file
     *
     * @var string
     */
    protected $_logFile = 'sdm_fileupload.log';

    /**
     * Allowed MIME types
     *
     * @var array
     */
    protected $_allowedMime = array(
        'image/jpeg',
        'image/gif',
        'image/png',
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/msword'
    );

    /**
     * Allowed file extensions
     *
     * @var array
     */
    protected $_allowedExt = array(
        'jpg',
        'jpeg',
        'gif',
        'png',
        'pdf',
        'doc',
        'docx'
    );

    /**
     * Return the allowed MIME types
     *
     * @return array
     */
    public function getAllowedTypesMime()
    {
        return $this->_allowedMime;
    }

    /**
     * Return the allowed extensions
     *
     * @return array
     */
    public function getAllowedExtensions()
    {
        return $this->_allowedExt;
    }

    /**
     * Returns the media directory path of the extension
     *
     * @param bool $full = true yo return full path
     *
     * @return str
     */
    public function getMediaDirectoryPath($full = false)
    {
        if ($full) {
            return Mage::getBaseDir('media') . DS . 'uploads';
        } else {
            return 'media' . DS . 'uploads';
        }
    }
}
