<?php
/**
 * Separation Degrees Media
 *
 * Implements the product compatibility functionality.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Compatibility
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Compatibility_Model_Productline class
 */
class SDM_Compatibility_Model_Productline extends Mage_Core_Model_Abstract
{
    /**
     * Initialize
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('compatibility/productline');
    }

    /**
     * Generate the code before a save
     *
     * @return void
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        $code = Mage::helper('taxonomy')->transformNameToCode($this->getName());
        $this->setCode($code);
    }

    /**
     * Remove image file before a delete
     *
     * @return void
     */
    protected function _beforeDelete()
    {
        parent::_beforeDelete();

        Mage::helper('compatibility')->removeFile(
            Mage::getBaseDir('media') . DS . $this->getImageUrl()
        );
    }
}
