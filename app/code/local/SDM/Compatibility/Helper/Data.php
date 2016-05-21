<?php
/**
 * Separation Degrees Media
 *
 * Implements the product compatibility functionality.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Compatibility
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Compatibility_Helper_Data class
 */
class SDM_Compatibility_Helper_Data extends SDM_Core_Helper_Data
{
    /**
     * Overwriting core log file
     *
     * @var string
     */
    protected $_logFile = 'sdm_compatibility.log';

    protected $_allowedImageFileTypes = array('jpg', 'jpeg', 'gif', 'png');

    /**
     * Returns the possible types of products. Add as needed.
     *
     * @return array
     */
    public function getProductTypeArray()
    {
        return array(
            'die' => 'Die',
            'machine' => 'Machine',
            'supply' => 'Supply',
            'accessory' => 'Accessory',
        );
    }

    /**
     * Returns the media directory path of the extension
     *
     * @param bool $full To return full path
     *
     * @return str
     */
    public function getMediaDirectoryPath($full = false)
    {
        if ($full) {
            return Mage::getBaseDir('media') . DS . 'compatibility';
        } else {
            return 'compatibility';
        }
    }

    /**
     * Returns the allowed image file types
     *
     * @return array
     */
    public function getAllowedImageFileTyes()
    {
        return $this->_allowedImageFileTypes;
    }
}
