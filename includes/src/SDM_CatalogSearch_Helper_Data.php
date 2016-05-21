<?php
/**
 * Separation Degrees One
 *
 * Magento catalog search customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CatalogSearch
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_CatalogSearch_Helper_Data class
 */
class SDM_CatalogSearch_Helper_Data
    extends Mage_CatalogSearch_Helper_Data
{
    /**
     * Param get
     *
     * @return mixed
     */
    public function getParams()
    {
        return Mage::app()->getRequest()->getParams();
    }

    /**
     * Retrieve result page url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @param  string $query
     * @return string
     */
    public function getResultUrl($query = null)
    {
        return $this->_getUrl('catalogsearch/result', array(
            '_query' => array(self::QUERY_VAR_NAME => $query),
            '_forced_secure' => $this->_getApp()->getFrontController()->getRequest()->isSecure()
        ));
    }
}
