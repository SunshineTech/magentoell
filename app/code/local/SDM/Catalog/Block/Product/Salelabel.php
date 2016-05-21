<?php
/**
 * Separation Degrees Media
 *
 * Magento catalog customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Catalog
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Catalog_Block_Product_Salelabel class
 */
class SDM_Catalog_Block_Product_Salelabel
    extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * Internal constructor that is called from real constructor
     *
     * @return SDM_Catalog_Block_Product_Salelabel
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('sdm/product/salelabel-icon.phtml');
        return $this;
    }

    /**
     * Sets the display type for this sale label
     *
     * @param string $type
     *
     * @return SDM_Catalog_Block_Product_Salelabel
     */
    public function setDisplayType($type)
    {
        $type = $type === 'text' ? 'text' : 'icon';
        $this->setTemplate('sdm/product/salelabel-'.$type.'.phtml');
        return $this;
    }

    /**
     * Passes required data to helper to get sale label code
     *
     * @return string
     */
    public function getSaleCode()
    {
        if (!$this->hasSaleCode()) {
            $code = $this->getSaleLabelHelper()->getSaleLabelCode(
                $this->getProduct(),
                $this->getDiscountTypeApplied()
            );

            $this->setSaleCode($code);
        }

        return parent::getSaleCode();
    }

    /**
     * Passes required data to helper to get starburst percentage
     *
     * @return bool|int
     */
    public function getSalePercentage()
    {
        if (!$this->hasSalePercentage()) {
            $perc = false;
            if ($this->getSaleCode() === 'starburst') {
                $perc = $this->getSaleLabelHelper()->getStarburstPercentage(
                    $this->getProduct()
                );
                $perc = empty($perc) ? false : round($perc);
            }
            $this->setSalePercentage($perc);
        }

        return parent::getSalePercentage();
    }

    /**
     * Returns sale label helper
     *
     * @return SDM_Catalog_Helper_Salelabel
     */
    public function getSaleLabelHelper()
    {
        return Mage::helper('sdm_catalog/salelabel');
    }
}
