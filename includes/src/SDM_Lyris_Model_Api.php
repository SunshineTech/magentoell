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
 * Model for communicating with Lyris API
 */
class SDM_Lyris_Model_Api extends Varien_Object
{
    const OPTION_SEPARATOR = '||';

    /**
     * Config objects storage
     *
     * @return SDM_Lyris_Model_Config_Api
     */
    public function getConfig()
    {
        if (!$this->hasConfig()) {
            $this->setConfig(Mage::getSingleton('sdm_lyris/config_api'));
        }
        return parent::getConfig();
    }

    /**
     * Map Magento form fields to Lyris fields
     *
     * @param array $data
     *
     * @return array
     */
    protected function _mapData(array $data)
    {
        $datas = array();
        foreach ($data as $name => $value) {
            if ($name == 'email') {
                $datas[] = array(
                    'type'  => 'email',
                    'value' => $value,
                );
            }
            list($name, $value) = $this->_mapSpecial($name, $value);
            if ($name === false) {
                continue;
            }
            if (strpos($name, 'lyris_') === 0) {
                $id = substr($name, 6);
                if (is_numeric($id)) {
                    $datas[] = array(
                        'type'  => 'demographic',
                        'id'    => $id,
                        'value' => is_array($value) ? implode(self::OPTION_SEPARATOR, $value) : $value,
                    );
                }
            }
        }
        return $datas;
    }

    /**
     * Some attributes require special handling
     *
     * @param string $name
     * @param string $value
     *
     * @return array
     */
    protected function _mapSpecial($name, $value)
    {
        switch ($name) {
            case 'country_id':
                $id = $this->_getCountryIdForWebsite(Mage::app()->getWebsite()->getCode());
                $name  = 'lyris_' . $id;
                $value = Mage::getModel('directory/country')->loadByCode($value)->getName();
                break;
            case 'region_id':
                $id = $this->_getRegionIdForWebsite(Mage::app()->getWebsite()->getCode());
                if ($id === false) {
                    break;
                }
                $region = Mage::getModel('directory/region')->load($value);
                $name   = 'lyris_' . $id;
                $value  = $region->getCode();
                if (!$value) {
                    return array(false, false);
                }
                break;
        }
        return array($name, $value);
    }

    /**
     * Get the lyris attribute id for this website
     *
     * @param string $code
     *
     * @return integer
     */
    protected function _getCountryIdForWebsite($code)
    {
        switch ($code) {
            case SDM_Core_Helper_Data::WEBSITE_CODE_US:
                return 36031;
            case SDM_Core_Helper_Data::WEBSITE_CODE_UK:
                return 37852;
            case SDM_Core_Helper_Data::WEBSITE_CODE_ED:
                return 37782;
            case SDM_Core_Helper_Data::WEBSITE_CODE_ER:
                return 37853;
        }
    }

    /**
     * Get the lyris attribute id for this website
     *
     * @param string $code
     *
     * @return integer|boolean
     */
    protected function _getRegionIdForWebsite($code)
    {
        switch ($code) {
            case SDM_Core_Helper_Data::WEBSITE_CODE_US:
                return 37850;
            case SDM_Core_Helper_Data::WEBSITE_CODE_UK:
                return false;
            case SDM_Core_Helper_Data::WEBSITE_CODE_ED:
                return 37851;
            case SDM_Core_Helper_Data::WEBSITE_CODE_ER:
                return 37800;
        }
    }

    /**
     * Construct the XML input string to send to Lyris
     *
     * @param array   $data
     * @param boolean $requiresMlid
     * @param string  $additional
     *
     * @return string
     */
    protected function _buildInput(array $data = array(), $requiresMlid = false, $additional = '')
    {
        $input = '<DATASET>';
        $input .= '<SITE_ID>' . $this->getConfig()->getSiteId() . '</SITE_ID>';
        $password = $this->getConfig()->getPassword();
        if ($password) {
            $input .= $this->_buildData('extra', 'password', $password);
        }
        if ($requiresMlid) {
            $input .= '<MLID>' . $this->getConfig()->getMlid() . '</MLID>';
        }
        $data = $this->_mapData($data);
        foreach ($data as $detail) {
            $input .= $this->_buildData(
                $detail['type'],
                isset($detail['id']) ? $detail['id'] : false,
                $detail['value'],
                isset($detail['multi']) ? $detail['multi'] : false
            );
        }
        $input .= $additional;
        $input .= '</DATASET>';
        return $input;
    }

    /**
     * Construct single piece of data to be sent to Lyris
     *
     * @param string          $type
     * @param boolean|integer $id
     * @param string|array    $value
     * @param boolean         $multi
     *
     * @return string
     */
    protected function _buildData($type, $id, $value, $multi = false)
    {
        if (!$value) {
            return;
        }
        if (is_array($value)) {
            if (!$multi) {
                $return = '';
                foreach ($value as $v) {
                    $return .= $this->_buildData($type, $id, $v);
                }
                return $return;
            }
            $value = implode('||', $value);
        }
        return '<DATA type="' . $type . '"'
            . ($id !== false ? ' id="' . $id . '"' : '') . '><![CDATA['
            . $value . ']]></DATA>';
    }

    /**
     * Remove and funky characters from user input
     *
     * @param string|array $value
     *
     * @return string
     */
    protected function _sanitizeInput($value)
    {
        if (is_array($value)) {
            foreach ($value as &$v) {
                $v = $this->_sanitizeInput($v);
            }
            return $value;
        }
        return filter_var(
            $value,
            FILTER_SANITIZE_SPECIAL_CHARS,
            array(
                'flags' => FILTER_FLAG_STRIP_HIGH
            )
        );
    }

    /**
     * Make a post request
     *
     * @param array $fields
     *
     * @return stdClass
     */
    protected function _post(array $fields)
    {
        Mage::helper('sdm_lyris')->log('Sending request to ' . $this->getConfig()->getUrl());
        Mage::helper('sdm_lyris')->log($fields);
        return $this->_request($this->getConfig()->getUrl(), array(
            CURLOPT_POST       => count($fields),
            CURLOPT_POSTFIELDS => http_build_query($fields)
        ));
    }

    /**
     * Make a curl request
     *
     * @param string $url
     * @param array  $options
     *
     * @return stdClass
     */
    protected function _request($url, array $options = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        foreach ($options as $key => $value) {
            curl_setopt($ch, $key, $value);
        }
        $response     = curl_exec($ch);
        $result       = new stdClass;
        $result->code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize   = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        $result->header = $this->_parseHeader(substr($response, 0, $headerSize));
        $responseBody = substr($response, $headerSize);
        Mage::helper('sdm_lyris')->log($result);
        Mage::helper('sdm_lyris')->log($responseBody);
        $result->body = new Varien_Simplexml_Element($responseBody);
        return $result;
    }

    /**
     * Make the headers received from the curl request more readable
     *
     * @param string $rawData
     *
     * @return array
     */
    protected function _parseHeader($rawData)
    {
        $data = array();
        foreach (explode("\n", trim($rawData)) as $line) {
            $bits = explode(': ', $line);
            if (count($bits) > 1) {
                $key = $bits[0];
                unset($bits[0]);
                $data[$key] = trim(implode(': ', $bits));
            }
        }
        return $data;
    }
}
