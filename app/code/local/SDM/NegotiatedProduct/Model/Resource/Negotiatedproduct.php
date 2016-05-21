<?php
/**
 * Separation Degrees One
 *
 * Ellison's negotiated product prices
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_NegotiatedProduct
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_NegotiatedProduct_Model_Resource_Negotiatedproduct class
 */
class SDM_NegotiatedProduct_Model_Resource_Negotiatedproduct
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('negotiatedproduct/negotiatedproduct', 'id');
    }
}
