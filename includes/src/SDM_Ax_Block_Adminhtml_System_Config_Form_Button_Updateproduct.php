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
 * SDM_Ax_Block_Adminhtml_System_Config_Form_Button_Updateproduct
 */
class SDM_Ax_Block_Adminhtml_System_Config_Form_Button_Updateproduct
    extends SDM_Ax_Block_Adminhtml_System_Config_Form_Abstract
{
    /**
     * Set template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ax/system/config/button/update_product.phtml');
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'id'        => 'ax_update_product_button',
                'label'     => $this->helper('adminhtml')->__('Run'),
                'onclick'   => 'javascript:runProductUpdate(); return false;'
            ));

        return $button->toHtml();
    }
}
