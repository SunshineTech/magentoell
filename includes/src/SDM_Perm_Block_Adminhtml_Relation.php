<?php
/**
 * Separation Degrees One
 *
 * Adds more functionality to Amasty_Perm
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Perm
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Add website column
 */
class SDM_Perm_Block_Adminhtml_Relation extends Amasty_Perm_Block_Adminhtml_Relation
{
    /**
     * Prepare columns
     *
     * @return SDM_Perm_Block_Adminhtml_Relation
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        // Add it on the right side
        $this->addColumn('website_id', array(
            'header'    => Mage::helper('amperm')->__('Website'),
            'width'     => '150',
            'index'     => 'website_id',
            'type' => 'options',
            'options' => Mage::helper('sdm_core')->getAssociativeWebsiteNames()
        ));

        return $this;
    }
}
