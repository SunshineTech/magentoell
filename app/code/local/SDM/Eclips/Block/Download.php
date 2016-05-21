<?php
/**
 * Separation Degrees One
 *
 * eClips Software Download
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Eclips
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Eclips_Block_Download class
 */
class SDM_Eclips_Block_Download extends Mage_Core_Block_Template
{
    /**
     * PDF file names
     */
    const PDF_FILENAME_EN = 'eclips_Handheld_Remote_Control_Software_Install Guide_05052010_web.pdf';
    const PDF_FILENAME_FR = 'FR_eclips_Handheld_Remote_Control_Software_Install Guide_05052010_web.pdf';
    const PDF_FILENAME_SP = 'SP_eclips_Handheld_Remote_Control_Software_Install Guide_05052010_web.pdf';
    const PDF_FILENAME_GR = 'GR_eclips_Handheld_Remote_Control_Software_Install Guide_05052010_web.pdf';

    /**
     * Software file nanes
     */
    const SOFTWARE_FILENAME_WIN32 = 'win_eclips3.1_32bit.zip';
    const SOFTWARE_FILENAME_WIN64 = 'win_eclips3.1_64bit.zip';
    const SOFTWARE_FILENAME_OSX = 'mac_eclips3.1.dmg';

    /**
     * Checks if customer is logged in
     *
     * @return boole
     */
    public function isCustomerLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    /**
     * Returns the URL for the specified PDF guide
     *
     * @return str
     */
    public function getEnglishGuide()
    {
        return $this->_getMediaUrl() . self::PDF_FILENAME_EN;
    }

    /**
     * Returns the URL for the specified PDF guide
     *
     * @return str
     */
    public function getFrenchGuide()
    {
        return $this->_getMediaUrl() . self::PDF_FILENAME_FR;
    }

    /**
     * Returns the URL for the specified PDF guide
     *
     * @return str
     */
    public function getSpanishGuide()
    {
        return $this->_getMediaUrl() . self::PDF_FILENAME_SP;
    }

    /**
     * Returns the URL for the specified PDF guide
     *
     * @return str
     */
    public function getGermanGuide()
    {
        return $this->_getMediaUrl() . self::PDF_FILENAME_GR;
    }

    /**
     * Returns the URL for the specified software
     *
     * @return str
     */
    public function getWindows32BitSoftware()
    {
        return $this->_getMediaUrl() . self::SOFTWARE_FILENAME_WIN32;
    }

    /**
     * Returns the URL for the specified software
     *
     * @return str
     */
    public function getWindows64BitSoftware()
    {
        return $this->_getMediaUrl() . self::SOFTWARE_FILENAME_WIN64;
    }

    /**
     * Returns the URL for the specified software
     *
     * @return str
     */
    public function getOsXSoftware()
    {
        return $this->_getMediaUrl() . self::SOFTWARE_FILENAME_OSX;
    }

    /**
     * Get the base URL of the PDF locaiton
     *
     * @return str
     */
    protected function _getMediaUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)
            . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'pdfs'
            . DIRECTORY_SEPARATOR . 'eclips' . DIRECTORY_SEPARATOR;
    }
}
