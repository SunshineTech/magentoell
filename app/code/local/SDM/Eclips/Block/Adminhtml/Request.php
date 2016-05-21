<?php
/**
 * Separation Degrees One
 *
 * eClips Software Download
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Eclips
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Eclips_Block_Adminhtml_Request class
 */
class SDM_Eclips_Block_Adminhtml_Request
    extends Mage_Core_Block_Template
{
    /**
     * Get count
     *
     * @return integer
     */
    public function getCount()
    {
        return Mage::getModel('eclips/request')->load(1)->getCount();
    }
}
