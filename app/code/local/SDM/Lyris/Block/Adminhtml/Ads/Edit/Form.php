<?php
/**
 * Separation Degrees One
 *
 * Lyris Newsletter Management
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Lyris
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Edit Ads Form
 */
class SDM_Lyris_Block_Adminhtml_Ads_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Build the form
     * @return SDM_Lyris_Block_Adminhtml_Ads_Edit_Form
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
