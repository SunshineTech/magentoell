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
 * Abstract view for newsletter operations
 */
class SDM_Lyris_Block_Abstract extends Mage_Core_Block_Template
{
    /**
     * Get the name of this store's newsletter
     *
     * @return string
     */
    public function getNewsletterName()
    {
        return Mage::getSingleton('sdm_lyris/config_newsletter')->getName();
    }

    /**
     * Get existing data if possible
     *
     * @param string $name
     *
     * @return string|boolean
     */
    public function getValue($name)
    {
        $values = Mage::getSingleton('core/session')->getLyrisAccount();
        return isset($values[$name]) ? $values[$name] : false;
    }
}
