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
 * Render giftcard payment info block
 */
class SDM_Valutec_Block_Payment_Method_Info_Giftcard
    extends Mage_Payment_Block_Info
{
    /**
     * Set template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('sdm/valutec/payment/method/info/giftcard.phtml');
    }
}
