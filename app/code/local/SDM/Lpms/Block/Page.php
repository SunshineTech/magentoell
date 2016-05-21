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
 * SDM_Lpms_Block_Page class
 */
class SDM_Lpms_Block_Page extends Mage_Cms_Block_Page
{
    /**
     * Prepare HTML content
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = parent::_toHtml();
        $assets = Mage::getModel('lpms/asset')
            ->getCollection()
            ->filterByPageId($this->getPage()->getId())
            ->sortAssets();

        foreach ($assets as $asset) {
            // Append HTML to page content
            $html .= $asset->renderAsset();
        }

        return $html;
    }
}
