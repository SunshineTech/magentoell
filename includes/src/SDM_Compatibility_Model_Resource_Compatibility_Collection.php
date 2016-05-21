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
 * SDM_Compatibility_Model_Resource_Compatibility_Collection class
 */
class SDM_Compatibility_Model_Resource_Compatibility_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('compatibility/compatibility');
    }
}
