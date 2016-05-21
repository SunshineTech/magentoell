<?php
/**
 * Separation Degrees One
 *
 * Magento catalog customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Catalog
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Catalog_Model_Category class
 */
class SDM_Catalog_Model_Category extends Mage_Catalog_Model_Category
{
    /**
     * Returns an http'ed version of the filtering URL
     *
     * @return string
     */
    public function getFilteringRedirectUrl()
    {
        if (!$this->hasFilteringRedirectUrl()) {
            $filterParam = trim($this->getFilteringParameter());
            if (!empty($filterParam) && strpos($filterParam, "http") === 0) {
                $this->setFilteringRedirectUrl($filterParam);
            } elseif (!empty($filterParam)) {
                $filterParam = $filterParam[0] == "/" ? substr($filterParam, 1) : $filterParam;
                $this->setFilteringRedirectUrl(Mage::getBaseUrl() . $filterParam);
            } else {
                $this->setFilteringRedirectUrl(false);
            }
        }

        return parent::getFilteringRedirectUrl();
    }
}
