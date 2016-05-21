<?php
/**
 * Separation Degrees One
 *
 * Ellison's Teachers' Planning Calendar
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Calendar
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Formats the color cell in a grid
 */
class SDM_Calendar_Block_Adminhtml_Event_Grid_Column_Renderer_Color
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders grid column
     *
     * @param  Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $data = $row->getData($this->getColumn()->getIndex());
        return <<<HTML
<span style="background: #$data;min-width: 50px;padding: 0 5px;text-align: center;display: inline-block;">#$data</span>
HTML;
    }
}
