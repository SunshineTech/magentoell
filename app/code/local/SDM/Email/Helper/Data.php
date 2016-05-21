<?php
/**
 * Separation Degrees One
 *
 * Email template customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Email
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Email_Helper_Data class
 */
class SDM_Email_Helper_Data extends SDM_Core_Helper_Data
{
    /**
     * Log file
     *
     * @var string
     */
    protected $_logFile = 'sdm_email.log';

    /**
     * Get vat number
     *
     * @param string $countryId
     *
     * @return string
     */
    public function getVatNumber($countryId = 'US')
    {
        $countryId = strtolower($countryId);

        switch ($countryId) {
            case 'es':
                $vatNum = 'N8265373D';
                break;
            case 'it':
                $vatNum = '00617249994';
                break;
            default:
                $vatNum = '886263386';
                break;
        }

        return $vatNum;
    }
}
