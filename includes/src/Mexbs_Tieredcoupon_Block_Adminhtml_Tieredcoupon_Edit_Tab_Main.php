<?php
/**
 * Mexbs_Tieredcoupon_Block_Adminhtml_Tieredcoupon_Edit_Tab_Main
 * class that is used for displaying the general details form of the grouping coupon
 *
 * @copyright MexBS
 * @author MexBS <it@mexbs.com>
 */
class Mexbs_Tieredcoupon_Block_Adminhtml_Tieredcoupon_Edit_Tab_Main
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * getter for the tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('mexbs_tieredcoupon')->__('Tiered Coupon Information');
    }

    /**
     * getter for the tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('mexbs_tieredcoupon')->__('Tiered Coupon Information');
    }

    /**
     * gets whether the tab can be displayed
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * gets whether the tab is hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * prepares the form fields of the general details of the grouping coupon
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $groupincoupon = Mage::registry('current_tieredcoupon');


        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend' => Mage::helper('mexbs_tieredcoupon')->__('Details'))
        );

        if ($groupincoupon->getId()) {
            $fieldset->addField('tieredcoupon_id', 'hidden', array(
                'name' => 'tieredcoupon_id',
            ));
        }


        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => Mage::helper('mexbs_tieredcoupon')->__('Name'),
            'title' => Mage::helper('mexbs_tieredcoupon')->__('Name'),
            'required' => true,
        ));

        $fieldset->addField('description', 'textarea', array(
            'name' => 'description',
            'label' => Mage::helper('mexbs_tieredcoupon')->__('Description'),
            'title' => Mage::helper('mexbs_tieredcoupon')->__('Description'),
            'style' => 'height: 100px;',
        ));


        $fieldset->addField('code', 'text', array(
            'name' => 'tieredcoupon_code',
            'label' => Mage::helper('mexbs_tieredcoupon')->__('Code'),
            'title' => Mage::helper('mexbs_tieredcoupon')->__('Code'),
            'required' => true,
        ));

        $fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('mexbs_tieredcoupon')->__('Status'),
            'title'     => Mage::helper('mexbs_tieredcoupon')->__('Status'),
            'name'      => 'is_active',
            'required' => true,
            'options'    => array(
                '1' => Mage::helper('mexbs_tieredcoupon')->__('Active'),
                '0' => Mage::helper('mexbs_tieredcoupon')->__('Inactive'),
            ),
        ));

        if (!$groupincoupon->getId()) {
            $groupincoupon->setData('is_active', '1');
        }

        $form->setValues($groupincoupon->getData());
        $form->setUseContainer(false);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
