<?php
/**
 * Separation Degrees Media
 *
 * Ellison's Mage_Sales customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Sales
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Sales_Model_Quote class
 */
class SDM_Sales_Model_Quote extends Mage_Sales_Model_Quote
{
    /**
     * Trigger collect totals after loading, if required. However, it requires
     * a break due to Ellison's price comparison logic.
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _afterLoad()
    {
        // collect totals and save me, if required
        if (1 == $this->getData('trigger_recollect')) {
            if (is_null(Mage::registry('quote_recollect_triggered'))) {
                Mage::register('quote_recollect_triggered', 1);
            } elseif (Mage::registry('quote_recollect_triggered') >= 1) {
                $currentCount = Mage::registry('quote_recollect_triggered') + 1;
                Mage::unregister('quote_recollect_triggered');
                Mage::register('quote_recollect_triggered', $currentCount);
            }

            $this->collectTotals()->save();
        }
        return Mage_Core_Model_Abstract::_afterLoad();

        // @see Mage_Core_Model_Abstract::_afterLoad
        // Mage::dispatchEvent('model_load_after', array('object'=>$this));
        // Mage::dispatchEvent($this->_eventPrefix.'_load_after', $this->_getEventData());
        // return $this;
    }

    /**
     * Returns true if minimum order amount requirement is satisfied when applicable
     *
     * @param  float $minAmount
     * @return bool
     */
    public function meetsMinimumOrderQuantity(&$minAmount)
    {
        $store = Mage::app()->getStore();
        if ($store->getWebsite()->getCode() !== SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_RE) {
            $minAmount = 0;
            return true;
        }

        if (Mage::helper('sdm_checkout')->isModuleEnabled('SDM_Customer')) {
            $helper = Mage::helper('sdm_customer');
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $hasOrdered = $helper->hasOrdered($customer, $store);

            if ($hasOrdered) {
                $minAmount = $helper->getMinOrderAmount($customer);
            } else {
                $minAmount = $helper->getMinFirstOrderAmount($customer);
            }

            if ($this->getSubtotal() >= $minAmount) {
                return true;
            }

            return false;

            // If SDM_Customer is disabled for some reason, bypass the check, to prevent a fatal error
        } else {
            $minAmount = 0;
            return true;
        }
    }
}
