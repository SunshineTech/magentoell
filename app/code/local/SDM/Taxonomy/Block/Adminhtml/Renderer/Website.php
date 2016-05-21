<?php
/**
 * Separation Degrees Media
 *
 * Ellison's custom product taxonomy implementation.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Taxonomy
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Taxonomy_Block_Adminhtml_Renderer_Website class
 */
class SDM_Taxonomy_Block_Adminhtml_Renderer_Website
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
        $tagId = $row->getId();
        $websiteIds = Mage::getResourceModel('taxonomy/item_date')
            ->getWebsiteIdsByTaxonomyId($tagId);

        $codes = Mage::helper('sdm_core')->getAssociativeEllisonSystemCodes();
        $html = "";

        if (count($websiteIds) > 0) {
            foreach ($websiteIds as $id) {
                if (isset($codes[$id])) {
                    $html .= "{$codes[$id]},";
                }
            }
        }

        return trim($html, ',');
    }
}
