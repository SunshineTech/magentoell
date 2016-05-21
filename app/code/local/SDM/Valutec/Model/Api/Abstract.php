<?php
/**
 * Separation Degrees One
 *
 * Valutec Giftcard Integration
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Valutec
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Base interactions with Valutec API
 */
abstract class SDM_Valutec_Model_Api_Abstract extends Varien_Object
{
    /**
     * Make a call of specified type
     *
     * @param  string $command
     * @param  array  $args
     * @return stdClass
     */
    abstract public function call($command, array $args = array());

    /**
     * Make an API call
     *
     * @param  string $type
     * @param  string $command
     * @param  array  $args
     * @return stdClass
     */
    protected function _call($type, $command, array $args = array())
    {
        $helper     = Mage::helper('sdm_valutec');
        $client     = $this->getClient();
        $identifier = $this->getIdentifier();
        $args       = array_merge($args, array(
            'ClientKey'  => $this->getClientKey(),
            'Identifier' => $identifier,
            'ServerID'   => $this->getServerId(),
            'TerminalID' => $this->getTerminalId(),
        ));
        $helper->debug('Request "' . $type . '_' . $command . '" from ' . $this->getUrl() . ' with arguments:');
        $helper->debug($args);
        $response = $client->{$type . '_' . $command}($args);
        $helper->debug('Response:');
        $helper->debug($response);
        $result   = $response->{$type . '_' . $command . 'Result'};
        if (strlen($result->ErrorMsg) > 0) {
            throw new SDM_Valutec_Exception($result->ErrorMsg);
        }
        if ($result->Identifier !== $identifier) {
            throw new SDM_Valutec_Exception('Security error.  Call has been terminated');
        }
        return $result;
    }

    /**
     * Create and return soap client
     *
     * @return SoapClient
     */
    public function getClient()
    {
        if (!$this->hasClient()) {
            $this->setClient(new SoapClient(
                $this->getUrl(),
                array('trace' => 1)
            ));
        }
        return $this->getData('client');
    }

    /**
     * Get API endpoint url
     *
     * @return string
     */
    public function getUrl()
    {
        return Mage::getStoreConfig(SDM_Valutec_Helper_Data::XML_PATH_CONFIG_API_URL);
    }

    /**
     * Get API client key
     *
     * @return string
     */
    public function getClientKey()
    {
        return Mage::getStoreConfig(SDM_Valutec_Helper_Data::XML_PATH_CONFIG_API_CLIENT_KEY);
    }

    /**
     * Get API terminal id
     *
     * @return string
     */
    public function getTerminalId()
    {
        return Mage::getStoreConfig(SDM_Valutec_Helper_Data::XML_PATH_CONFIG_API_TERMINAL_ID);
    }

    /**
     * Get API server id
     *
     * @return string
     */
    public function getServerId()
    {
        return Mage::getStoreConfig(SDM_Valutec_Helper_Data::XML_PATH_CONFIG_API_SERVER_ID);
    }

    /**
     * Create a unique identifier for this request
     *
     * @return string
     */
    public function getIdentifier()
    {
        return substr(sha1(time()), 0, 10);
    }
}
