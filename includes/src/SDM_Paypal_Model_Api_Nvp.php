<?php
/**
 * Separation Degrees One
 *
 * Modifications to Mage_Paypal
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Paypal
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Paypal_Model_Api_Nvp model
 */
class SDM_Paypal_Model_Api_Nvp extends Mage_Paypal_Model_Api_Nvp
{
    /**
     * DoExpressCheckoutPayment request/response map. NOTIFYURL parameter removed.
     *
     * @var array
     */
    protected $_doExpressCheckoutPaymentRequest = array(
        'TOKEN', 'PAYERID', 'PAYMENTACTION', 'AMT', 'CURRENCYCODE', 'IPADDRESS', 'BUTTONSOURCE',
        'RETURNFMFDETAILS', 'SUBJECT', 'ITEMAMT', 'SHIPPINGAMT', 'TAXAMT',
    );
}
