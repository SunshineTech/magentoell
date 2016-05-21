<?php
/**
 * Separation Degrees Media
 *
 * Embed Youtube Videos and Playlists
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_YoutubeFeed
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * Process key file upload
 */
class SDM_YoutubeFeed_Model_Adminhtml_System_Config_Backend_File_Key
    extends Mage_Adminhtml_Model_System_Config_Backend_File
{
    /**
     * The tail part of directory path for uploading
     */
    const UPLOAD_DIR = 'sdm_youtubefeed';

    /**
     * Token for the root part of directory path for uploading
     */
    const UPLOAD_ROOT = 'media';

    /**
     * Return path to directory for upload file
     *
     * @return string
     */
    protected function _getUploadDir()
    {
        return $this->_getUploadRoot(self::UPLOAD_ROOT)
            . '/' . self::UPLOAD_DIR;
    }

    /**
     * Getter for allowed extensions of uploaded files.
     *
     * @return array
     */
    protected function _getAllowedExtensions()
    {
        return array('p12');
    }

    /**
     * Get real media dir path
     *
     * @param  string $token
     * @return string
     */
    protected function _getUploadRoot($token)
    {
        return Mage::getBaseDir($token);
    }
}
