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
 * SDM_Compatibility_Model_Resource_Productline class
 */
class SDM_Compatibility_Model_Resource_Productline extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('compatibility/productline', 'productline_id');
    }

    /**
     * Returns the taxonomy record ID
     *
     * @param str $code
     *
     * @return int
     */
    public function getIdByCode($code)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from($this->getTable('compatibility/productline'), 'productline_id')
            ->where('code = ?', (string)$code);
        $result = $adapter->fetchCol($select);

        if (!empty($result)) {
            return reset($result);
        }
    }
}
