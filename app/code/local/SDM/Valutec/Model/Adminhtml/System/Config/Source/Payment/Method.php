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
 * System config for payment methods
 */
class SDM_Valutec_Model_Adminhtml_System_Config_Source_Payment_Method
{
    /**
     * List all payment methods
     *
     * @return array
     */
    public function toOptionArray()
    {
        $methods = Mage::helper('payment')->getPaymentMethodList(true, true);
        foreach ($methods as &$method) {
            if (empty($method['label'])) {
                $method['label'] = $method['value'];
            }
        }
        return $methods;
    }
}
