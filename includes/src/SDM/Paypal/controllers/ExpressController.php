<?php
/**
 * Separation Degrees One
 *
 * Modifications to Paypal submission controller to add lifecycle checks
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Paypal
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

require_once Mage::getModuleDir('controllers', 'Mage_Paypal') . DS . 'ExpressController.php';

/**
 * Express Checkout Controller
 */
class SDM_Paypal_ExpressController extends Mage_Paypal_ExpressController
{
    /**
     * Review order after returning from PayPal
     *
     * @return null
     */
    public function reviewAction()
    {
        // Validate that the items here are valid checkout items
        if (!Mage::helper('sdm_checkout/quote')->validateForCheckout()) {
            $this->_redirect('checkout/cart');
            return;
        }

        return parent::reviewAction();
    }

    /**
     * Submit the order
     *
     * @return null
     */
    public function placeOrderAction()
    {
        // Validate that the items here are valid checkout items
        if (!Mage::helper('sdm_checkout/quote')->validateForCheckout()) {
            $this->_redirect('checkout/cart');
            return;
        }

        return parent::placeOrderAction();
    }
}
