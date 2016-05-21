<?php
/**
 * Separation Degrees One
 *
 * Magento catalog rule customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CatalogRule
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */
 
/**
 * SDM_CatalogRule_Block_Adminhtml_Promo_Catalog_Edit_Form class
 */
class SDM_CatalogRule_Block_Adminhtml_Promo_Catalog_Edit_Form
    extends Mage_Adminhtml_Block_Promo_Catalog_Edit_Form
{
    /**
     * Prepare form fields
     *
     * @return SDM_CatalogRule_Block_Adminhtml_Promo_Catalog_Edit_Form
     */
    protected function _prepareForm()
    {
        $return = parent::_prepareForm();

        $this->getForm()->setEnctype('multipart/form-data');

        return $return;
    }
}
