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
 * Configuration model for api config data
 */
class SDM_Lyris_Model_Config_Api extends SDM_Lyris_Model_Config_Abstract
{
    protected $_xmlPathConfigGroup = 'api';

    /**
     * Gets the API url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getConfig('url');
    }

    /**
     * Gets the API password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->getConfig('password');
    }

    /**
     * Gets the site id
     *
     * @return string
     */
    public function getSiteId()
    {
        return $this->getConfig('site_id');
    }

    /**
     * Gets the MLID
     *
     * @return string
     */
    public function getMlid()
    {
        return $this->getConfig('mlid');
    }
}
