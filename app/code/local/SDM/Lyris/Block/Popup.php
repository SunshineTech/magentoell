<?php
/**
 * Separation Degrees One
 *
 * Lyris Newsletter Management
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Lyris
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * View for newsletter popup window
 */
class SDM_Lyris_Block_Popup extends SDM_Lyris_Block_Abstract
{
    /**
     * Get the action url for this form
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('newsletter/account/save');
    }

    /**
     * Determines if the popup should be visible
     *
     * @return boolean
     */
    public function isVisible()
    {
        if (!Mage::getSingleton('sdm_lyris/config_popup')->isActive()) {
            return false;
        }
        if ($this->helper('sdm_lyris')->hasCookie()) {
            return false;
        }
        return true;
    }

    /**
     * Only show popup html if module is enabled
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->isVisible()) {
            return '';
        }
        $this->helper('sdm_lyris')->setCookie(
            Mage::getSingleton('sdm_lyris/config_popup')->getDismissDays()
        );
        return parent::_toHtml();
    }
}
