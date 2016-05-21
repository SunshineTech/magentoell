<?php
/**
 * Separation Degrees One
 *
 * Banner Ads
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Banner
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */
/**
 * SDM_Banner_Model_Stores class
 */
class SDM_Banner_Model_Stores
    extends Mage_Core_Model_Abstract
{
    /**
     * Initialize
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('slider/stores');
    }
}
