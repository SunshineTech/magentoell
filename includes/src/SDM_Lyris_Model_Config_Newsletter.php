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
 * Configuration model for newsletter config data
 */
class SDM_Lyris_Model_Config_Newsletter extends SDM_Lyris_Model_Config_Abstract
{
    protected $_xmlPathConfigGroup = 'newsletter';

    /**
     * Get the name of this store's newsletter
     *
     * @return string
     */
    public function getName()
    {
        return $this->getConfig('name');
    }

    /**
     * Get the URL path to the success page
     *
     * @return string
     */
    public function getSuccessUrl()
    {
        return $this->getConfig('success');
    }
}
