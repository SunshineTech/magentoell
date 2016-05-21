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
 * SDM_Lpms_Block_Adminhtml_Cms_Page_Edit_Form class
 */
class SDM_Lpms_Block_Adminhtml_Cms_Page_Edit_Form
    extends Mage_Adminhtml_Block_Cms_Page_Edit_Form
{
    /**
     * Prepare form
     *
     * @return SDM_Lpms_Block_Adminhtml_Cms_Page_Edit_Form
     */
    protected function _prepareForm()
    {
        $return = parent::_prepareForm();

        $this->getForm()->setEnctype('multipart/form-data');

        return $return;
    }
}
