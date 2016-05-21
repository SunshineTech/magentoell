<?php
/**
 * Separation Degrees Media
 *
 * Store switcher for UK
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_StoreSwitcher
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_StoreSwitcher_Block_Switcher class
 */
class SDM_StoreSwitcher_Block_Switcher
    extends Mage_Core_Block_Template
{
    /**
     * Get the class for the switcher link
     *
     * @return string
     */
    public function getClass()
    {
        return 'currency-link currency-' . $this->helper('sdm_storeswitcher')->getCurrency();
    }

    /**
     * Get the URL for switching to the other store
     *
     * @return string
     */
    public function getSwitcherUrl()
    {
        return $this->getUrl('') . '?___store='
            . $this->helper('sdm_storeswitcher')->getOtherStoreCode();
    }

    /**
     * Get symbol
     *
     * @return mixed
     */
    public function getSymbol()
    {
        return $this->helper('sdm_storeswitcher')->getCurrencySymbol();
    }
}
