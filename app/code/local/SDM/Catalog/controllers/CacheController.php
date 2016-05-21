<?php
/**
 * Separation Degrees Media
 *
 * Magento catalog customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Catalog
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

$base = Mage::getModuleDir('controllers', 'Mage_Adminhtml');
require_once $base . DS . "CacheController.php";

/**
 * SDM_Catalog_CacheController class
 */
class SDM_Catalog_CacheController
    extends Mage_Adminhtml_CacheController
{
    /**
     * Clean JS/css files cache
     *
     * @return void
     */
    public function cleanCatalogImagesAction()
    {
        $_mediaDir = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'category' . DS;
        $_cacheDir = $_mediaDir . 'cache' . DS;

        try {
            array_map('unlink', glob($_cacheDir . "*"));
            Mage::dispatchEvent('clean_catalog_image_cache_after');
            $this->_getSession()->addSuccess(
                Mage::helper('adminhtml')->__('The catalog image cache has been cleaned.')
            );
        } catch (Exception $e) {
            $this->_getSession()->addException(
                $e,
                Mage::helper('adminhtml')->__('An error occurred while clearing the catalog image cache.')
            );
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*');
    }

    /**
     * Clean resized images cache
     *
     * @return void
     */
    public function cleanResizedImagesAction()
    {
        $_mediaDir = Mage::getBaseDir('media') . DS . 'resized' . DS;
        try {
            $this->_rrmdir($_mediaDir);
            mkdir($_mediaDir);
            Mage::dispatchEvent('clean_resized_image_cache_after');
            $this->_getSession()->addSuccess(
                Mage::helper('adminhtml')->__('The resized image cache has been cleaned.')
            );
        } catch (Exception $e) {
            $this->_getSession()->addException(
                $e,
                Mage::helper('adminhtml')->__('An error occurred while clearing the resized image cache.')
            );
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*');
    }

    /**
     * Recursive remove dir
     *
     * @param  string $dir
     * @return void
     */
    private function _rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") {
                        $this->_rrmdir($dir."/".$object);
                    } else {
                        unlink($dir."/".$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
}
