<?php
/**
 * Separation Degrees One
 *
 * Banner Ads
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Banner
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */
/**
 * SDM_Banner_Helper_Data class
 */
class SDM_Banner_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Deletes directory
     *
     * @param string $fileUrl
     *
     * @return void
     */
    public function deleteDir($fileUrl)
    {
        try {
            $io = new Varien_Io_File();
            $result = $io->rmdir($fileUrl, true);
        } catch (Exception $e) {
            // @todo: handle error
            Mage::helper('banner')->log($e->getMessage());
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        }
    }
}
