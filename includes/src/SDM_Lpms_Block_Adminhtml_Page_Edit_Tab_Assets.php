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
 * SDM_Lpms_Block_Adminhtml_Page_Edit_Tab_Assets class
 */
class SDM_Lpms_Block_Adminhtml_Page_Edit_Tab_Assets
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Initialize
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Prepare form
     *
     * @return SDM_Lpms_Block_Adminhtml_Page_Edit_Tab_Assets
     */
    protected function _prepareForm()
    {
        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('page_');

        $model = Mage::registry('cms_page');

        $fieldset = $form->addFieldset(
            'assets_fieldset',
            array(
                'legend' => Mage::helper('lpms')->__('Assets Data'),
                'class' => 'fieldset-wide'
            )
        );

        $fieldset->addField('lpms_asset_data', 'hidden', array(
            'name' => 'lpms_asset_data',
            'label' => Mage::helper('lpms')->__('Asset Data'),
            'title' => Mage::helper('lpms')->__('Asset Data'),
            'disabled'  => $isElementDisabled
        ));

        Mage::dispatchEvent('adminhtml_lpms_page_edit_tab_assets_prepare_form', array('form' => $form));

        $form->setValues(array(
            'lpms_asset_data' => Mage::helper('lpms')->getPageAssetsAsJson($model->getId())
        ));

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('lpms')->__('Assets');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('lpms')->__('Assets');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param  string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('cms/page/' . $action);
    }
}
