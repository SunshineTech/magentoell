<?php
/**
 * Separation Degrees One
 *
 * Lyris Newsletter Management
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Lyris
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Ads resource model
 */
class SDM_Lyris_Model_Resource_Ads
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sdm_lyris/ads', 'id');
    }
}
