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
 * SDM_Sales_Model_Resource_Setup class
 */
class SDM_Sales_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup
{
    /**
     * Creates or updates an order status and associates it to a state
     *
     * @param string  $label
     * @param string  $status
     * @param string  $state
     * @param boolean $isStateDefault
     *
     * @return void
     */
    public function updateStatus($label, $status, $state, $isStateDefault = false)
    {
        // Update status
        $orderStatus = Mage::getModel('sales/order_status')->load($status);   // loads by status?
        try {
            $orderStatus->setStatus($status)
                ->setLabel($label)
                ->save();
            // Assign state
            $orderStatus->assignState($state, $isStateDefault);

        } catch (Exception $e) {
            Mage::helper('sdm_ax')->log(
                "Failed to update order status/state: $label/$state. Error: "
                    . $e->getMessage()
            );
        }
    }
}
