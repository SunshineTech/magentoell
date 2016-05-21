<?php
/**
 * Separation Degrees Media
 *
 * This module handles all the store locator functionality for Ellison.
 *
 * The original code from this module is based off the FME_Gmapstrlocator module. We converted their
 * module to an SDM module rather than extending from it because the amount of modifications and
 * rewrites necessary for it to fit Ellison's spec were extensive, yet we still felt there was value
 * in using FME's module as a starting point.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Gmapstrlocator
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator_Edit_Form class
 */
class SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form
     *
     * @return SDM_Gmapstrlocator_Block_Adminhtml_Gmapstrlocator_Edit_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
            ));

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
