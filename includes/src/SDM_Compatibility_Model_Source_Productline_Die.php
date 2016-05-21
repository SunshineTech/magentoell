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
 * SDM_Compatibility_Model_Source_Productline_Die class
 */
class SDM_Compatibility_Model_Source_Productline_Die
    extends SDM_Compatibility_Model_Source_Productline
{
    const CODE = 'die';

    /**
     * Return the options array
     *
     * @return array
     */
    public function getAllNameOptions()
    {
        $collection = Mage::getModel('compatibility/productline')
            ->getCollection()
            ->addFieldToSelect(array('productline_id', 'name'))
            ->addFieldToFilter('type', self::CODE);

        return $this->_toNameArray($collection);
    }
}
