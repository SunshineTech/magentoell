<?php
/**
 * Separation Degrees One
 *
 * Magento catalog customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Checkout
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Purchase order usage depends on the ERUS customer's flag
 */
class SDM_Checkout_Block_Onepage_Payment_Methods
    extends Mage_Checkout_Block_Onepage_Payment_Methods
{
    /**
     * Check payment method model
     *
     * @param Mage_Payment_Model_Method_Abstract|null $method
     *
     * @return bool
     */
    protected function _canUseMethod($method)
    {
        // PO method must be turned on
        if ($method && $method->canUseCheckout()) {
            // Only for allowed ERUS and all EEUS customers
            if ($method->getCode() === SDM_Checkout_Helper_Data::PURCHASE_ORDER_PAYMENT_CODE) {
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                if ($customer->getCanUsePurchaseOrder()
                    || Mage::app()->getWebsite()->getCode() === SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_ED
                ) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return parent::_canUseMethod($method);
            }
        }

        return false;
    }
}
