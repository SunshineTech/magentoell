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
 * Helper object for api interaction
 */
class SDM_Valutec_Helper_Api extends Mage_Core_Helper_Abstract
{
    /**
     * Get all card programs
     *
     * @return array
     */
    public function getCardPrograms()
    {
        return array(
            'gift'         => $this->__('Original Gift Card Program'),
            'promotional'  => $this->__('Promotional Card (Test)'),
            'combo'        => $this->__('Original Combo Card Program'),
            'auto_rewards' => $this->__('Auto Rewards (Loyalty Only)'),
            'loyalty'      => $this->__('Original Loyalty Card Program'),
        );
    }

    /**
     * Get the full value from a program code
     *
     * @param  string $code
     * @return string
     */
    public function getCardProgram($code)
    {
        $value = Mage::getStoreConfig('sdm_valutec/api/card_program/' . $code);
        if ($value == null) {
            throw new SDM_Valutec_Exception('Card program is not valid');
        }
        return $value;
    }
}
