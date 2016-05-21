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
 * Lyris helper
 */
class SDM_Lyris_Helper_Data extends Mage_Core_Helper_Abstract
{
    const LOG_FILE    = 'sdm_lyris.log';
    const COOKIE_NAME = 'sdm_lyris_popup';

    /**
     * Logs message to file
     *
     * @param mixed $message
     *
     * @return void
     */
    public function log($message)
    {
        return;
        Mage::log($message, null, self::LOG_FILE);
    }

    /**
     * Set a cookie that disables the popup from showing
     *
     * @param integer $days
     *
     * @return void
     */
    public function setCookie($days)
    {
        Mage::getSingleton('core/cookie')
            ->set(self::COOKIE_NAME, 1, 3600 * 24 * (integer) $days);
    }

    /**
     * Determine if the popup cookie has been set
     *
     * @return boolean
     */
    public function hasCookie()
    {
        return Mage::getSingleton('core/cookie')->get(self::COOKIE_NAME);
    }
}
