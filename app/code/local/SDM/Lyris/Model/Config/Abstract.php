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
 * Configuration model for config data
 */
class SDM_Lyris_Model_Config_Abstract extends Varien_Object
{
    const XML_PATH_CONFIG_SECTION = 'sdm_lyris';

    /**
     * Gets sdm_lyris config
     *
     * @param string $field
     *
     * @return string|array
     */
    public function getConfig($field)
    {
        return $this->_getConfig($field);
    }

    /**
     * Gets sdm_lyris config flag
     *
     * @param string $field
     *
     * @return boolean
     */
    public function getConfigFlag($field)
    {
        return $this->_getConfig($field, true);
    }

    /**
     * Get the path for this config
     *
     * @param string $field
     *
     * @return string
     */
    public function getConfigPath($field)
    {
        return sprintf(
            '%s/%s/%s',
            self::XML_PATH_CONFIG_SECTION,
            $this->_xmlPathConfigGroup,
            $field
        );
    }

    /**
     * Gets sdm_lyris api config
     *
     * @param string  $field
     * @param boolean $flag
     *
     * @return string|array|boolean|null
     */
    protected function _getConfig($field, $flag = false)
    {
        $configPath = $this->getConfigPath($field);
        $scope = $this->getScope();
        $value = null;
        if (isset($scope['website'])) {
            $value = Mage::app()->getWebsite($scope['website'])
                ->getConfig($configPath);
            $node = Mage::getConfig()->getNode('default/' . $configPath);
            if (!empty($node['backend_model']) && !empty($value)) {
                $backend = Mage::getModel((string) $node['backend_model']);
                $backend->setPath($configPath)->setValue($value)->afterLoad();
                $value = $backend->getValue();
            }
        } elseif (isset($scope['store'])) {
            $value = Mage::app()->getStore($scope['store'])
                ->getConfig($configPath);
        } else {
            if ($flag) {
                return Mage::getStoreConfigFlag($configPath);
            }
            return Mage::getStoreConfig($configPath);
        }
        if ($flag) {
            return !empty($value) && 'false' !== $value;
        }
        return $value;
    }
}
