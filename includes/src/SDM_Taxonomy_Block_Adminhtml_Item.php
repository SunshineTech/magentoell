<?php
/**
 * Separation Degrees Media
 *
 * Ellison's custom product taxonomy implementation.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Taxonomy
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Taxonomy_Block_Adminhtml_Item class
 */
class SDM_Taxonomy_Block_Adminhtml_Item
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_blockGroup = 'sdm_taxonomy_adminhtml';
        $this->_controller = 'item';
        $this->_headerText = Mage::helper('taxonomy')
            ->__('Manage Taxonomy');
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl(
            'adminhtml/taxonomy_item/edit'
        );
    }
}
