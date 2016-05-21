<?php
/**
 * Separation Degrees One
 *
 * Ellison's Mage_Customer customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Customer
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Customer_Block_Account_Navigation class
 */
class SDM_Customer_Block_Account_Navigation extends Mage_Customer_Block_Account_Navigation
{
    /**
     * Remove links by their name
     *
     * @param  string $name
     * @return $this
     */
    public function removeLinkByName($name)
    {
        if (isset($this->_links[$name])) {
            unset($this->_links[$name]);
        }
        return $this;
    }

    /**
     * Exclude saved CC tab if SFC Cybersource is not enabled
     *
     * @return array
     */
    public function getLinks()
    {
        $links = array();

        foreach ($this->_links as $link) {
            if ($link->getName() == 'creditcards') {
                if (Mage::getStoreConfig('payment/sfc_cybersource/active', Mage::app()->getStore())) {
                    $links[] = $link;
                }
            } else {
                $links[] = $link;
            }
        }

        return $links;
    }
}
