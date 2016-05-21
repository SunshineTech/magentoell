<?php
/**
 * Separation Degrees Media
 *
 * Modifications to Magento's Page Module
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Page
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * Rewrite of SDM_Page_Block_Html_Pager
 */
class SDM_Page_Block_Html_Pager extends Mage_Page_Block_Html_Pager
{
    /**
     * Added forced secure option
     *
     * @param  array $params
     * @return string
     */
    public function getPagerUrl($params = array())
    {
        $urlParams = array();
        $urlParams['_current']       = true;
        $urlParams['_escape']        = true;
        $urlParams['_forced_secure'] = Mage::app()->getStore()->isCurrentlySecure();
        $urlParams['_use_rewrite']   = true;
        $urlParams['_query']         = $params;
        return $this->getUrl('*/*/*', $urlParams);
    }

    /**
     * Returns the previous-page-URL with correct previous page number
     *
     * @return int
     */
    public function getPreviousPageUrl()
    {
        return $this->getPageUrl($this->getCollection()->getCurPage() - 1);
    }

    /**
     * Returns the previous-page-URL with correct next page number
     *
     * @return int
     */
    public function getNextPageUrl()
    {
        return $this->getPageUrl($this->getCollection()->getCurPage() + 1);
    }
}
