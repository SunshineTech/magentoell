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
 * SDM_GeoIP_Helper_Data class
 */
class SDM_GeoIP_Helper_Data extends SDM_Core_Helper_Data
{
    /**
     * The current country code
     *
     * @var null
     */
    protected $_code = null;

    /**
     * Returns the current country code as a string
     *
     * @return string
     */
    public function getCountryCode()
    {
        if ($this->_code === null) {
            // Open library
            include_once "SDM/GeoIP/GeoIP.inc";
            $geoIP = geoip_open("lib/SDM/GeoIP/GeoIP.dat", GEOIP_STANDARD);

            // Get code and save to helper
            $code = geoip_country_code_by_addr($geoIP, $_SERVER['REMOTE_ADDR']);
            $code = is_string($code) ? strtoupper($code) : null;
            $this->_code = $code;

            // Close library
            geoip_close($geoIP);
        }
        return $this->_code;
    }

    /**
     * Returns array of countries that, if on UK, should be prompted to go to US
     *
     * @return array
     */
    public function getSendToUs()
    {
        return array(
            'US'   // United States
        );
    }

    /**
     * Returns array of countries that, if on US, should be prompted to go to UK
     *
     * @return array
     */
    public function getSendToUk()
    {
        return array(
            'UK', // United Kingdom
            'AL', // Albania
            'AD', // Andorra
            'AT', // Austria
            'BY', // Belarus
            'BE', // Belgium
            'BA', // Bosnia and Herzegovina
            'BG', // Bulgaria
            'HR', // Croatia
            'CY', // Cyprus
            'CZ', // Czech Republic
            'DK', // Denmark
            'EE', // Estonia
            'FI', // Finland
            'FR', // France
            'DE', // Germany
            'GI', // Gibraltar
            'GB', // Great Britan
            'GR', // Greece
            'HU', // Hungary
            'VA', // Holdy See (Vatican City State)
            'IS', // Iceland
            'IE', // Ireland (Republic of)
            'IT', // Italy
            'LV', // Latvia
            'LI', // Liechtenstein
            'LT', // Lithuania
            'LU', // Luxembourg
            'MK', // Macedonia
            'MT', // Malta
            'MD', // Moldova, Republic of
            'MC', // Monaco
            'NL', // Netherlands
            'NO', // Norway
            'PL', // Poland
            'PT', // Portugal
            'RO', // Romania
            'SM', // San Marino
            'SK', // Slovakia
            'SI', // Slovenia
            'ES', // Spain
            'SE', // Sweden
            'CH', // Switzerland
            'TR', // Turkey
            'UA'  // Ukraine
        );
    }
}
