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
 * Check balance of giftcard
 */
class SDM_Valutec_CheckController extends Mage_Core_Controller_Front_Action
{
    /**
     * Render form
     *
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout()
            ->renderLayout();
    }
}
