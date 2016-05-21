<?php
/**
 * Separation Degrees One
 *
 * Ellison and Sizzix Shipping Rules
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Shipping
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Shipping config object
 */
class SDM_Shipping_Model_Config extends Varien_Object
{
    const XML_PATH_CONFIG_SURCHARGE = 'sdm_shipping/general/surcharge';

    /**
     * Determines if surchage logic is enabled
     *
     * @return boolean
     */
    public function getIsSurchargeEnabled()
    {
        if (!$this->hasIsSurchargeEnabled()) {
            $this->setIsSurchargeEnabled(Mage::getStoreConfigFlag(self::XML_PATH_CONFIG_SURCHARGE));
        }
        return parent::getIsSurchargeEnabled();
    }
}
