<?php
/**
 * Separation Degrees One
 *
 * eCal Lite download request extension
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Eclips
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Eclips_Model_Resource_Request class
 */
class SDM_Eclips_Model_Resource_Request
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize table and PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eclips/request', 'id');
    }
}
