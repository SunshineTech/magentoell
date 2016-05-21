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
 * SDM_StoreSwitcher_Helper_Data class
 */
class SDM_StoreSwitcher_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * Check if we're on the UK site
     *
     * @return bool
     */
    public function showSwitcher()
    {
        return Mage::app()->getWebsite()->getCode() === 'sizzix_uk';
    }

    /**
     * Get the currency for this store
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->_storeToCurrency(Mage::app()->getStore()->getCode());
    }

    /**
     * Get the currency for the other store
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return Mage::app()->getLocale()->currency($this->getCurrency())->getSymbol();
    }

    /**
     * Get the store code for the other store
     *
     * Because we're never going to have more than 2 store views ಠ_ಠ
     *
     * @return string
     */
    public function getOtherStoreCode()
    {
        return Mage::app()->getStore()->getCode() === 'sizzix_uk_bp' ? 'sizzix_uk_eu' : 'sizzix_uk_bp';
    }

    /**
     * Convert a store code to it's currency
     *
     * @param  string $store
     * @return string
     */
    public function _storeToCurrency($store)
    {
        return $store === 'sizzix_uk_eu' ? 'EUR' : 'GBP';
    }
}
