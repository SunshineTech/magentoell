<?php
/**
 * Separation Degrees One
 *
 * SDM's address verification extension
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_AddressVerification
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_AddressVerification_Helper_Data class
 */
class SDM_AddressVerification_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * Constants for the helper
     */
    const USPS_CODE = 'usps';

    const XML_PATH_ENALBED = 'addressverification/usps_address_verification/enabled';
    const XML_PATH_USPS_TEST_MODE = 'addressverification/usps_address_verification/test_mode';
    const XML_PATH_USPS_ACCESS_KEY = 'addressverification/usps_address_verification/usps_access_key';

    const RESPONSE_CODE_VALIDATION_FAILED = '-2147219401';
    const RESPONSE_CODE_API_FAILED = '80040b1a';
    const RESPONSE_CODE_TEST_SERVER_ERROR = '-2147219040';
    const RESPONSE_CODE_EXTENSION_DISABLED = 'disabled';

    /**
     * Uses IWD_AddressVerification's address verification methods.
     *
     * @param Mage_Customer_Model_Address|array $address
     * @param str                               $type
     *
     * @return bool|
     */
    public function verifyAddress($address, $type)
    {
        if (!$this->isEnabled()) {
            return array(
                'error' => false,
                'code' => SDM_AddressVerification_Helper_Data::RESPONSE_CODE_EXTENSION_DISABLED,
                'message' => 'IWD_AddressVerification is diabled. Allowed to proceed '
                    . 'without validation.',
                'candidates' => null,
                'original_address' => null
            );
        }

        if ($address instanceof Mage_Customer_Model_Address) {
            $data = $address->getData();
        } else {
            $data = $address;
        }

        // Only works with USPS at initial release of this feature
        if ($type === self::USPS_CODE) {
            try {
                return $this->_verifyAddressUsps($data);
            } catch (Exception $e) {
                return array(
                    'error' => true,
                    'code' => null,
                    'message' => $e->getMessage(),
                    'candidates' => null,
                    'original_address' => null
                );
            }

        } else {
            return false;
        }
    }

    /**
     * Verify the address data with USPS
     *
     * @param array $data
     *
     * @throws Mage_Core_Exception
     *
     * @return array Result array
     */
    protected function _verifyAddressUsps($data)
    {
        if (isset($data['country_id']) && !empty($data['country_id']) && $data['country_id'] == 'US') {
            if (!empty($data['street']) && !empty($data['city']) && !empty($data['postcode']) && !empty($data[ 'region_id'])) {
                $regionModel = Mage::getModel('directory/region')->load($data['region_id']);
                $regionId = $regionModel->getCode();
                $data = $this->_cleanAddressData($data);

                include_once $this->getLibPath() . 'XMLParser.php';
                include_once $this->getLibPath() . 'usps/USPSAddressVerify.php';

                if (empty($regionId)) {
                    Mage::throwException('Region ID is missing');
                }

                $checkAddress = array(
                    'street' => $data['street'],
                    'city' => $data['city'],
                    'state' => $regionId,
                    'zip_code' => $data['postcode'],
                    'country' => 'US',
                );

                // Set up USPS verification objects
                $verify = $this->_getUspsAddressVerificationObject();
                $uspsAddress = $this->_getUspsAddressObject($data);
                $addressInfo = $uspsAddress->getAddressInfo();
                $verify->addAddress($uspsAddress);

                // Perform the request and return result
                $verify->verify();
                $response = $verify->getArrayResponse();

                if ($verify->isSuccess()) {
                    // Get lis of addresses
                    $candidates = $this->_getUspsCandidates($response);
                    // Mage::log($candidates);

                    // check if candidate address is differ from entered
                    if (empty($candidates)) {
                        return array(
                            'error' => false,
                            'code' => null,
                            'candidates' => array(),
                            'original_address' => $checkAddress
                        );
                    }

                    // Check if any address match for 100%
                    $match = false;
                    foreach ($candidates as $cand) {
                        // Compare state
                        if (strtolower($cand['region_abbr']) != strtolower($checkAddress['state'])) {
                            continue;
                        }

                        // compare zip
                        if ($cand['postcode'] != $checkAddress['zip_code']) {
                            $zipParts1 = explode('-', $cand['postcode']);
                            $zipForm = str_replace(' ', '-', $checkAddress['zip_code']);
                            $zipParts2 = explode('-', $zipForm);

                            if ($zipParts1[0] != $zipParts2[0]) {
                                continue;
                            }
                        }

                        // From USPS
                        $addr1  = strtolower($cand['street']);
                        $city1  = strtolower($cand['city']);
                        // From form
                        $addr2  = strtolower($addressInfo['Address2']);
                        $city2  = strtolower($checkAddress['city']);

                        // Compare street
                        $p1 = strpos($addr1, $addr2);
                        if ($p1 === false) {
                            $p1 = strpos($addr2, $addr1);
                        }
                        if ($p1 === false) {
                            continue;
                        }

                        // Compare city
                        $p2 = strpos($city1, $city2);
                        if ($p2 === false) {
                            $p2 = strpos($city2, $city1);
                        }
                        if ($p2 === false) {
                            continue;
                        }

                        $match = true;
                        break;
                    }

                    if ($match) {
                        $message = '';
                    } else {
                        $message = 'Verified address did not match the input address';
                    }

                    // Successful response
                    $response = array(
                        'error' => false,
                        'code' => null,
                        'message' => $message,
                        'candidates' => $candidates,
                        'original_address' => $checkAddress
                    );

                    return $response;

                } else {
                    $errorCode = $verify->getErrorCode();
                    $errorCode = strtolower($errorCode);

                    // Unsuccessful responses
                    if ($errorCode == self::RESPONSE_CODE_VALIDATION_FAILED) {
                        return array(
                            'error' => true,
                            'code' => $errorCode,
                            'message' => 'Address could not be validated as entered. '
                                . 'Please review your address and make necessary changes.',
                            'candidates' => array(),
                            'original_address' => $checkAddress
                        );
                    } elseif ($errorCode == self::RESPONSE_CODE_API_FAILED) {
                        return array(
                            'error' => false,   // Failed verification but since API itself failed, pass it
                            'message' => 'API Authorization failure. User is not '
                                . 'authorized to use API Verify. Allowed to procced '
                                . 'without validation.',
                            'code' => $errorCode,
                            'candidates' => array(),
                            'original_address' => $checkAddress
                        );
                    } elseif ($errorCode == self::RESPONSE_CODE_TEST_SERVER_ERROR) {
                        return array(
                            'error' => true,
                            'message' => 'This Information has not been included in this Test Server.',
                            'code' => $errorCode,
                            'candidates' => array(),
                            'original_address' => $checkAddress
                        );
                    }
                }

            } else {
                Mage::throwException('Address data is missing street, city, postcode, and/or region_id');
            }

        } else {
            Mage::throwException('Address data is missing country_id and/or not US');
        }
    }

    /**
     * Returns candidate addresses from the USPS response
     *
     * @param array $response
     *
     * @return array
     */
    protected function _getUspsCandidates($response)
    {
        $validAddresses = array();
        $usStates = array();
        $states = Mage::getModel('directory/country')->load('US')->getRegions();

        foreach ($states as $state) {
            $usStates[$state->getCode()] = $state->getId();
        }

        if (isset($response['AddressValidateResponse'])) {
            if (isset($response['AddressValidateResponse']['Address'])) {
                $validCandidate = $this->_parseUspsCandidate($response['AddressValidateResponse']['Address']);
                if (!empty($validCandidate)) {
                    $validCandidate['region'] = $usStates[$validCandidate['region_abbr']];
                    $validAddresses[] = $validCandidate;
                }
            }
        }

        return $validAddresses;
    }

    /**
     * Parses an address array
     *
     * @param array $candidate
     *
     * @return array
     */
    protected function _parseUspsCandidate($candidate)
    {
        if (!isset($candidate['Address2']) || empty($candidate['Address2'])) {
            return false;
        }

        $add = array();
        $add['street'] = $candidate['Address2'];
        if (isset($candidate['Address1']) && !empty($candidate['Address1'])) {
            $add['street'].= ' '.$candidate['Address1'];
        }

        if (!isset($candidate['City']) || empty($candidate['City'])) {
            return false;
        }
        $add['city'] = $candidate['City'];

        if (!isset($candidate['State']) || empty($candidate['State'])) {
            return false;
        }
        $add['region_abbr'] = strtoupper($candidate['State']);

        if (!isset($candidate['Zip5']) || empty($candidate['Zip5'])) {
            return false;
        }
        $add['postcode'] = $candidate['Zip5'];

        if (isset($candidate['Zip4']) && !empty($candidate['Zip4'])) {
            $add['postcode'].= '-'.$candidate['Zip4'];
        }

        return $add;
    }

    /**
     * Get the IWD_AddressVerification library path
     *
     * @return str
     */
    public function getLibPath()
    {
        return Mage::getBaseDir('lib') . '/iwd/verification/';
    }

    /**
     * Returns the USPS verify object
     *
     * @return USPSAddressVerify
     */
    protected function _getUspsAddressVerificationObject()
    {
        $key = Mage::getStoreConfig(self::XML_PATH_USPS_ACCESS_KEY);
        $testMode = (bool)Mage::getStoreConfig(self::XML_PATH_USPS_TEST_MODE);
        if (empty($key)) {
            return false;
        }

        $verify = new USPSAddressVerify($key);

        if ($testMode) {
            $verify->setTestMode(true);
        } else {
            $verify->setTestMode(false);
        }

        return $verify;
    }

    /**
     * Returns the USPS verify object
     *
     * @param array $data
     *
     * @return USPSAddress
     */
    protected function _getUspsAddressObject($data)
    {
        $uspsAddress = new USPSAddress;

        if (isset($data['company']) && !empty($data['company'])) {
            $uspsAddress->setFirmName($data['company']);
        }

        $streetInfo = $data['streets'];
        $street1 = '';
        $street2 = '';
        if (is_array($streetInfo)) {
            $street1 = $streetInfo[0];
            if (isset($streetInfo[1])) {
                $street2 = $streetInfo[1];
            }
        } else {
            $street1 = $data['street'];
        }

        $uspsAddress->setApt($street2);
        $uspsAddress->setAddress($street1);
        $uspsAddress->setCity($data['city']);
        $uspsAddress->setState($data['region_id']);

        $zip = trim($data['postcode']);
        $zip = str_replace(' ', '-', $zip);
        $zP = explode('-', $zip);

        $zip4 = '';
        $zip5 = $zP[0];
        if (isset($zP[1]) && !empty($zP[1])) {
            $zip4 = $zP[1];
        }

        $uspsAddress->setZip5($zip5);
        $uspsAddress->setZip4($zip4);

        return $uspsAddress;
    }

    /**
     * Clean up the address data
     *
     * @param array $data
     *
     * @return array
     */
    protected function _cleanAddressData($data)
    {
        $data['streets'] = $data['street'];
        $data['street'] = trim(implode(' ', $data['street']));
        $data['street'] = strip_tags($data['street']);
        $data['street'] = str_replace("\r\n", ", ", $data['street']);
        $data['street'] = str_replace("\n\r", ", ", $data['street']);
        $data['street'] = str_replace("\r", ", ", $data['street']);
        $data['street'] = str_replace("\n", ", ", $data['street']);
        $data['street'] = str_replace(",", "", $data['street']);

        return $data;
    }

    /**
     * Is enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_ENALBED);
    }
}
