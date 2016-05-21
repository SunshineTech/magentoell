<?php
/**
 * Separation Degrees One
 *
 * IP Address Based Messaging
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_GeoIP
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_GeoIP_Block_Geoip class
 */
class SDM_GeoIP_Block_Sitemessage extends Mage_Core_Block_Template
{
    /**
     * Returns a two digit country code as a string
     *
     * @param  string $code
     * @return string
     */
    public function checkCountryCode($code)
    {
        // If we already set a cookie, return null
        if ($this->checkCookie()) {
            return null;
        }

        // Otherwise set it...
        $this->setCookie();

        // And check for a country match
        if ($code === 'UK') {
            return in_array(
                Mage::helper('geoip')->getCountryCode(),
                Mage::helper('geoip')->getSendToUk()
            );
        } elseif ($code === 'US') {
            return in_array(
                Mage::helper('geoip')->getCountryCode(),
                Mage::helper('geoip')->getSendToUs()
            );
        } else {
            return Mage::helper('geoip')->getCountryCode() === strtoupper($code);
        }
    }

    /**
     * Set cookie stating we've check the IP address
     *
     * @return null
     */
    public function setCookie()
    {
        Mage::getModel('core/cookie')->set('geoip_msg_shown', '1', 60*60*24*90);
        return null;
    }

    /**
     * Returns true if the cookie has been set already
     *
     * @return bool
     */
    public function checkCookie()
    {
        return Mage::getModel('core/cookie')->get('geoip_msg_shown') === '1';
    }
}
