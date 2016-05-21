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
 * Edit calendar form
 */
class SDM_Calendar_Block_Adminhtml_Calendar_Edit_Form
extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Build the form
     *
     * @return SDM_Calendar_Block_Adminhtml_Calendar_Edit_Form
     */
    protected function _prepareForm()
    {
        $this->setForm(new Varien_Data_Form(array(
            'id'            => 'edit_form',
            'action'        => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method'        => 'post',
            'enctype'       => 'multipart/form-data',
            'use_container' => true
        )));
        return parent::_prepareForm();
    }
}
