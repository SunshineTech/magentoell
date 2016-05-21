<?php
/**
 * Separation Degrees One
 *
 * Ellison's AX ERP integration
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Ax
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Ax_Block_Adminhtml_System_Config_Form_Abstract
 */
abstract class SDM_Ax_Block_Adminhtml_System_Config_Form_Abstract
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Button html
     *
     * @return string
     */
    abstract public function getButtonHtml();

    /**
     * Return ajax url for button
     *
     * @param string $action
     *
     * @return string
     */
    public function getAjaxActionUrl($action = '')
    {
        return Mage::helper('adminhtml')->getUrl("adminhtml/ax/$action");
    }

    /**
     * Return element html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }
}
