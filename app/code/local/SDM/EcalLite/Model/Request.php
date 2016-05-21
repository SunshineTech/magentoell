<?php
/**
 * Separation Degrees One
 *
 * eCal Lite download request extension
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_EcalLite
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_EcalLite_Model_Request class
 */
class SDM_EcalLite_Model_Request extends Mage_Core_Model_Abstract
{
    /**
     * A saved instance of this follow's entity
     *
     * @var object
     */
    protected $_entityInstance = null;

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ecallite/request');
    }
}
