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
 * SDM_Lpms_Block_Asset class
 */
class SDM_Lpms_Block_Asset
    extends Mage_Core_Block_Template
{
    /**
     * Has slider
     *
     * @return boolean
     */
    public function hasSlider()
    {
        $hasSlider = $this->getData('has_slider');
        if ($hasSlider === null) {
            $hasSlider = $this->getAsset()->getData('image_format') === 'slider';
            $this->setData('has_slider', $hasSlider);
        }
        return $hasSlider;
    }

    /**
     * Has text
     *
     * @return boolean
     */
    public function hasText()
    {
        $hasText = $this->getData('has_text');
        if ($hasText === null) {
            $hasText = $this->getAsset()->getData('image_format') === 'imgtxt';
            $this->setData('has_text', $hasText);
        }
        return $hasText;
    }

    /**
     * Get horizontal count
     *
     * @return integer
     */
    public function horizontalCount()
    {
        $horCount = $this->getData('horizontal_count');
        if ($horCount === null) {
            $horCount = false;
            switch ($this->getAsset()->getData('image_format')) {
                case '1hor':
                    $horCount = 1;
                    break;
                case '2hor':
                    $horCount = 2;
                    break;
                case '3hor':
                    $horCount = 3;
                    break;
            }
            $this->setData('horizontal_count', $horCount);
        }
        return $horCount;
    }

    /**
     * Get asset image max
     *
     * @return integer
     */
    public function getAssetImageMax()
    {
        $max = $this->getData('max');
        if ($max === null) {
            switch ($this->getAsset()->getData('image_format')) {
                case '1hor':
                case 'imgtxt':
                    $max = 1;
                    break;
                case '2hor':
                    $max = 2;
                    break;
                case '3hor':
                    $max = 3;
                    break;
            }
            $assetImages = $this->getAsset()->getVisibleAssetImages();
            if (!empty($assetImages)) {
                $assetCount = count($assetImages);
                if ($assetCount && $assetCount > 0) {
                    $max = empty($max) || $max > $assetCount ? $assetCount : $max;
                }
            }
            $this->setData('max', $max);
        }
        return $max;
    }

    /**
     * Get image start tag
     *
     * @return string
     */
    public function getAssetImageStartTag()
    {
        if ($this->hasSlider() || $this->horizontalCount() !== false) {
            return "<li>";
        }
        return "";
    }

    /**
     * Get image end tag
     *
     * @return string
     */
    public function getAssetImageEndTag()
    {
        if ($this->hasSlider() || $this->horizontalCount() !== false) {
            return "</li>";
        }
        return "";
    }
}
