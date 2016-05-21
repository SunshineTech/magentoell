<?php
/**
 * Separation Degrees One
 *
 * Checkout-related customization
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Checkout
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Checkout_Block_Cart_Shipping class
 */
class SDM_Checkout_Block_Cart_Shipping extends Mage_Checkout_Block_Cart_Shipping
{
    /**
     * Address Model
     *
     * @var null
     */
    protected $_defaultAddress = null;

    /**
     * Default address
     *
     * @return mixed
     */
    public function getDefaultAddress()
    {
        if ($this->_defaultAddress === null) {
            $this->_defaultAddress = Mage::getSingleton('customer/session')
                ->getCustomer()
                ->getDefaultShippingAddress();
        }
        return empty($this->_defaultAddress) ? $this->getAddress() : $this->_defaultAddress;
    }

    /**
     * Get Estimate Country Id
     *
     * @return string
     */
    public function getEstimateCountryId()
    {
        $id = $this->getAddress()->getCountryId();
        if (empty($id)) {
            return $this->getDefaultAddress()->getCountryId();
        }
        return $id;
    }

    /**
     * Get Estimate Postcode
     *
     * @return string
     */
    public function getEstimatePostcode()
    {
        $code = $this->getAddress()->getPostcode();
        if (empty($code)) {
            return $this->getDefaultAddress()->getPostcode();
        }
        return $code;
    }

    /**
     * Get Estimate City
     *
     * @return string
     */
    public function getEstimateCity()
    {
        $city =  $this->getAddress()->getCity();
        if (empty($city)) {
            return $this->getDefaultAddress()->getCity();
        }
        return $city;
    }

    /**
     * Get Estimate Region Id
     *
     * @return mixed
     */
    public function getEstimateRegionId()
    {
        $id = $this->getAddress()->getRegionId();
        if (empty($id)) {
            return $this->getDefaultAddress()->getRegionId();
        }
        return $id;
    }

    /**
     * Get Estimate Region
     *
     * @return string
     */
    public function getEstimateRegion()
    {
        $region = $this->getAddress()->getRegion();
        if (empty($region)) {
            return $this->getDefaultAddress()->getRegion();
        }
        return $region;
    }
}
