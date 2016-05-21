<?php
/**
 * Separation Degrees One
 *
 * Updates to Auguria_Sliders
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Auguria
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Auguria_Block_Catalog_Category_Slider
 */
class SDM_Auguria_Block_Catalog_Category_Slider extends Auguria_Sliders_Block_Catalog_Category_Slider
{
    /**
     * No description provided from aguria
     *
     * @param  object $slide
     * @param  int    $width
     * @param  int    $height
     * @return string
     */
    public function getResizedMobileImage($slide, $width = null, $height = null)
    {
        // If base image exists
        if (is_file($this->getMobileMediaPath($slide))) {
            // If no resize return base image url
            if ($width==null && $height==null) {
                return $this->getMobileMediaUrl($slide);
            } elseif (!is_file($this->getMobileMediaPath($slide, $width, $height))) {
                // If resized image doesn't exists : process resize
                $imageObj = new Varien_Image($this->getMobileMediaPath($slide));
                $imageObj->constrainOnly(true);
                $imageObj->keepAspectRatio(true);
                $imageObj->keepFrame(false);
                $imageObj->resize($width, $height);
                $imageObj->save($this->getMobileMediaPath($slide, $width, $height));
                // If resized image exists : return resized url
                if (is_file($this->getMobileMediaPath($slide, $width, $height))) {
                    return $this->getMobileMediaUrl($slide, $width, $height);
                }
            } else {
                // Resized image exists : return it
                return $this->getMobileMediaUrl($slide, $width, $height);
            }
        }
        return '';
    }

    /**
     * No description provided from aguria
     *
     * @param  object $slide
     * @param  int    $width
     * @param  int    $height
     * @return string
     */
    public function getMobileMediaPath($slide, $width = null, $height = null)
    {
        $baseName = basename($slide->getImageMobile());
        if ($width==null && $height==null) {
            return Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) .
                DS . 'auguria' . DS . 'sliders' . DS . $baseName;
        }
        return Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) .
            DS . 'auguria' . DS . 'sliders' . DS . 'resized' . DS . $width.'x'.$height . DS . $baseName;
    }
    
    /**
     * No description provided from aguria
     *
     * @param  object $slide
     * @param  int    $width
     * @param  int    $height
     * @return string
     */
    public function getMobileMediaUrl($slide, $width = null, $height = null)
    {
        $baseName = basename($slide->getImageMobile());
        if ($width==null && $height==null) {
            return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'auguria/sliders/' . $baseName;
        }
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)
            . 'auguria/sliders/resized/' . $width.'x'.$height . '/' . $baseName;
    }

    /**
     * No description provided from aguria
     *
     * @param  object $slide
     * @return bool
     */
    public function displayImageMobile($slide)
    {
        $imagePath = $this->getMobileMediaPath($slide);
        return is_file($imagePath);
    }
}
