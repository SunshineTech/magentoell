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
 * Configuration model for popup config data
 */
class SDM_Lyris_Model_Config_Popup extends SDM_Lyris_Model_Config_Abstract
{
    protected $_xmlPathConfigGroup = 'popup';

    /**
     * Determines if the popup is active
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->getConfigFlag('active');
    }

    /**
     * Get days to hide popup when dismissed
     *
     * @return boolean
     */
    public function getDismissDays()
    {
        return $this->getConfig('cookie_days_dismiss');
    }

    /**
     * Get days to hide popup after conversion
     *
     * @return boolean
     */
    public function getConvertDays()
    {
        return $this->getConfig('cookie_days_convert');
    }
}
