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
 * SDM_Compatibility_Block_Adminhtml_Renderer_Website class
 */
class SDM_Compatibility_Block_Adminhtml_Renderer_Website
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders the website ID column of the grid
     *
     * @param Varien Object $row
     *
     * @return str HTML
     */
    public function render(Varien_Object $row)
    {
        $websites = explode(',', $row->getWebsiteIds());
        $codes = Mage::helper('sdm_core')->getAssociativeEllisonSystemCodes();
        $html = "";

        if (count($websites) > 0) {
            foreach ($websites as $code) {
                if (isset($codes[$code])) {
                    $html .= "{$codes[$code]},";
                }
            }
        }

        return trim($html, ',');
    }
}
