<?php
/**
 * Separation Degrees One
 *
 * Implements the customer/retailer discount logic for viewing and obtaining
 * discounts and prices, as wel as managing the customer groups.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CustomerDiscount
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_CustomerDiscount_Block_Adminhtml_Discountgroup_Grid_Renderer_Actions class
 */
class SDM_CustomerDiscount_Block_Adminhtml_Discountgroup_Grid_Renderer_Actions
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders grid column
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $editHtml = '<a href="'. $this->getUrl('*/*/edit/', array('_current' => true, 'id' => $row->getId())) .'">' .
                $this->__('Edit') .'</a> | ';

        return sprintf('%s%s<a href="%s" onClick="deleteConfirm(\'%s\', this.href); return false;">%s</a>',
            '',
            $editHtml,
            $this->getUrl('*/*/delete/', array(
                '_current'=>true,
                'id' => $row->getId(),
                Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->helper('core/url')->getEncodedUrl()
            )),
            Mage::helper('adminnotification')->__('Are you sure?'),
            Mage::helper('adminnotification')->__('Remove')
        );
    }
}
