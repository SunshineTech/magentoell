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
 * Configuration model for account config data
 */
class SDM_Lyris_Model_Config_Account extends SDM_Lyris_Model_Config_Abstract
{
    protected $_xmlPathConfigGroup = 'account';

    /**
     * Determines if the account section active
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->getConfigFlag('active');
    }
}
