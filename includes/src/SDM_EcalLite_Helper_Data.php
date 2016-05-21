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
 * SDM_EcalLite_Helper_Data class
 */
class SDM_EcalLite_Helper_Data extends SDM_Core_Helper_Data
{
    /**
     * eCal Lite responses. For reference only.
     */
    const K_REDEEM_ERROR_NONE = 0;
    const K_REDEEM_ERROR_DB_CONNECTION_FAILED = 1;
    const K_REDEEM_ERROR_INVALID_CODE = 2;
    const K_REDEEM_ERROR_INVALID_FIRST_NAME = 3;
    const K_REDEEM_ERROR_INVALID_LAST_NAME = 4;
    const K_REDEEM_ERROR_INVALID_EMAIL = 5;
    const K_REDEEM_ERROR_CODE_ALREADY_USED = 6;
    const K_REDEEM_ERROR_FAILED_TO_ADD_CUSTOMER = 7;
    const K_REDEEM_ERROR_FAILED_TO_MAKE_LICENSE = 8;
    const K_REDEEM_ERROR_FAILED_TO_SAVE_LICENSE = 9;
    const K_REDEEM_ERROR_FAILED_TO_MARK_CODE_AS_USED = 10;

    /**
     * Only one status
     */
    const STATUS_AUTHORIZED_CODE = 'authorized';
    const STATUS_AUTHORIZED_LABEL = 'Authorized';

    /**
     * URLs
     */
    const GET_URL = 'https://www.craftedge.com/ecal_lite/redeem.php';
    const SUPPORT_URL = 'http://www.craftedge.com/support/lostserial.php';

    /**
     * Log file
     *
     * @var string
     */
    protected $_logFile = 'sdm_ecallite.log';

    /**
     * Make a request to eCal Lite for authorization
     *
     * @param array $data
     *
     * @return str
     */
    public function request($data)
    {
        $code = $this->buildCode($data['codes']);
        $post = array(
            'code' => $code,
            'fname' => $data['firstname'],
            'lname' => $data['lastname'],
            'email' => $data['email']
        );

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => $this->getGetUrl($post),
                CURLOPT_RETURNTRANSFER => true,
            )
        );

        $response = curl_exec($curl);

        curl_close($curl);

        // For some reason there's a trailing semi-colon
        $response = trim($response, ';');

        return (string)$response;
    }

    /**
     * Process the response code
     *
     * Codes and meanings:
     *     0: Successfull
     *     1,7,8,9: Some kind of error
     *     2: Invalid code
     *     6: Activation code alread used
     *     3,4,5: Incorrect input
     *     10: Failed to mark code as used
     *
     * @param str $response
     *
     * @return array
     */
    public function processResponse($response)
    {
        $response = (string)$response;
        $result = array('success' => false, 'message' => '', 'response' => $response);

        switch ($response) {
            case '0':
                $result['success'] = true;
                $result['message'] = 'You have successfully submitted your eCal '
                    . 'Lite code. An email with the download link to your eCal '
                    . 'Lite software will be sent to your inbox shortly.';
                break;
            case '1':
            case '7':
            case '8':
            case '9':
            case '10':
                $result['message'] = 'There was an error in processing your request '
                    . 'to submit your eCAL lite activation code.  Please try entering '
                    . 'your activation code again. If you have any further questions '
                    . 'about submitting your eCAL lite activation code, please contact '
                    . '<a href="mailto:support@craftedge.com" target="_top">support@craftedge.com</a>'
                    . ' with your serial number, email address, '
                    . 'and first and last name.';
                break;
            case '2':
                $result['message'] = 'Unfortunately, the activation code entered '
                . 'does not match our records. Please re-enter activation code, '
                . 'making sure to enter activation code exactly as it appears on '
                . 'the activation card.';
                break;
            case '6':
                $result['message'] = "We're sorry. The activation code entered has "
                    . ' already been activated. Please request a new activation '
                    . 'link by providing us the same email address used at the '
                    . 'time of activation '
                    . '<a href="'. $this->getSupportUrl() .'" target="_blank">HERE</a>.';
                break;
            case '3':
            case '4':
            case '5':
                $result['message'] = 'Oops, invalid data found in one or more fields. '
                    . 'Please correct and resubmit.';
                break;
            default:
                $result['message'] = 'There was an error processing your request. '
                    . 'Please try again later or email support@craftedge.com.';
                break;
        }

        return $result;
    }

    /**
     * Returns the associative status array
     *
     * @return array
     */
    public function getStatuses()
    {
        $statuses = array(
            SDM_EcalLite_Helper_Data::STATUS_AUTHORIZED_CODE
                => SDM_EcalLite_Helper_Data::STATUS_AUTHORIZED_LABEL
        );

        return $statuses;
    }

    /**
     * Returns the associative website array
     *
     * @return array
     */
    public function getWebsiteArray()
    {
        $data = array();
        $websites = Mage::app()->getWebsites();

        foreach ($websites as $website) {
            $data[$website->getId()] = $website->getName();
        }

        return $data;
    }

    /**
     * Returns code array in its corrected string form
     *
     * @param array $codes
     *
     * @return str
     */
    public function buildCode($codes)
    {
        foreach ($codes as &$code) {
            $code = trim($code);
        }

        return (string)implode('-', $codes);
    }

    /**
     * Returns the GET URL for the eCal Lite authentication
     *
     * @param array $params
     *
     * @return str
     */
    public function getGetUrl($params)
    {
        return self::GET_URL . '?' . http_build_query($params);
    }

    /**
     * Returns the eCal Lite support URL
     *
     * @return str
     */
    public function getSupportUrl()
    {
        return self::SUPPORT_URL;
    }
}
