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
 * SDM_Taxonomy_Block_Adminhtml_Renderer_Date class
 */
class SDM_Taxonomy_Block_Adminhtml_Renderer_Date
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
        $date = trim($row->getData($this->getColumn()->getIndex()));

        if (empty($date)) {
            return '';
        }

        return date('Y-m-d', strtotime($date));
    }
}
