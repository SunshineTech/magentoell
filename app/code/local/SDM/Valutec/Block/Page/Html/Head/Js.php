<?php
/**
 * Separation Degrees One
 *
 * Valutec Giftcard Integration
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Valutec
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * JS snippets
 */
class SDM_Valutec_Block_Page_Html_Head_Js
    extends Mage_Core_Block_Template
{
    /**
     * Get AJAX apply url
     *
     * @return string
     */
    public function getUrlApply()
    {
        return $this->getUrl('sdm_valutec/giftcard/apply', array('_secure' => true));
    }

    /**
     * Get AJAX balance check url
     *
     * @return string
     */
    public function getUrlBalance()
    {
        return $this->getUrl('sdm_valutec/giftcard/balance', array('_secure' => true));
    }

    /**
     * Get AJAX remove url
     *
     * @return string
     */
    public function getUrlRemove()
    {
        return $this->getUrl('sdm_valutec/giftcard/remove', array('_secure' => true));
    }
}
