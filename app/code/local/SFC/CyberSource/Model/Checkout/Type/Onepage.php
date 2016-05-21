<?php
/**
 * StoreFront CyberSource Tokenized Payment Extension for Magento
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of StoreFront Consulting, Inc.
 *
 * @category  SFC
 * @package   SFC_CyberSource
 * @author    Garth Brantley <garth@storefrontconsulting.com>
 * @copyright 2009-2013 StoreFront Consulting, Inc. All Rights Reserved.
 * @license   http://www.storefrontconsulting.com/media/downloads/ExtensionLicense.pdf StoreFront Consulting Commercial License
 * @link      http://www.storefrontconsulting.com/cybersource-saved-credit-cards-extension-for-magento/
 *
 */

/**
 * One page checkout processing model
 */
class SFC_CyberSource_Model_Checkout_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{
    /**
     * Create order based on checkout type. Create customer if necessary.
     *
     * @throws Exception
     * @return Mage_Checkout_Model_Type_Onepage
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function saveOrder()
    {
        // Log
        // Mage::log('Magento is saving order...', Zend_Log::INFO, SFC_CyberSource_Helper_Data::LOG_FILE);

        // Get quote
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $this->getQuote();

        // Check if payment method is this extension
        if ($quote->getPayment()->getMethod() != SFC_CyberSource_Model_Method::METHOD_CODE) {
            // This is some other checkout method, just call parent saveOrder method
            parent::saveOrder();
        }
        else {
            // This the CyberSource ext checkout payment method
            // Log
            // Mage::log('This is a CyberSource order, handle any exceptions by cleaning up any created payment profiles.', Zend_Log::INFO, SFC_CyberSource_Helper_Data::LOG_FILE);
            // Get profiles created data from customer session
            $alreadyCreatedPaymentToken = Mage::getSingleton('customer/session')->getCreatedPaymentToken();

            // Wrap parent saveOrder method in a try / catch to handle any errors
            try {
                parent::saveOrder();
            }
            catch (Exception $eOrder) {
                // Log
                // Mage::log('Caught order exception in order with CyberSource payment method.', Zend_Log::INFO, SFC_CyberSource_Helper_Data::LOG_FILE);
                // Mage::log('Caught order exception: ' . $eOrder->getMessage(), Zend_Log::INFO, SFC_CyberSource_Helper_Data::LOG_FILE);
                // Catch any exception thrown during order saving process
                // Check if a new customer profile was created
                $createdPaymentToken = Mage::getSingleton('customer/session')->getCreatedPaymentToken();
                // Log
                // Mage::log('alreadyCreatedPaymentToken: ' . $alreadyCreatedPaymentToken, Zend_Log::INFO, SFC_CyberSource_Helper_Data::LOG_FILE);
                // Mage::log('createdCimPaymentProfileId: ' . $createdPaymentToken, Zend_Log::INFO, SFC_CyberSource_Helper_Data::LOG_FILE);
                // Check ids saved in session and decide if we need to delete anything
                if (strlen($createdPaymentToken) && $createdPaymentToken != $alreadyCreatedPaymentToken) {
                    // A new payment profile was created during this saveOrder, but exception was thrown and order aborted
                    // Log
                    // Mage::log('Order failed, but CyberSource payment profile was created.  Deleting profile: ' . $createdPaymentToken, Zend_Log::INFO, SFC_CyberSource_Helper_Data::LOG_FILE);
                    // Lets delete the payment profile that was created
                    try {
                        $customer = Mage::getSingleton('customer/session')->getCustomer();
                        /** @var SFC_CyberSource_Helper_Gateway $gatewayHelper */
                        $gatewayHelper = Mage::helper('sfc_cybersource/gateway');
                        $gatewayHelper->setConfigWebsite($customer->getWebsiteId());
                        $gatewayHelper->deletePaymentProfile($customer->getId(), $createdPaymentToken);
                    }
                    catch (\Exception $e) {
                        // Mage::log('Failed to delete payment profile: ' . $createdPaymentToken, Zend_Log::INFO, SFC_CyberSource_Helper_Data::LOG_FILE);
                        // Mage::log('Error: ' . $e->getMessage(), Zend_Log::INFO, SFC_CyberSource_Helper_Data::LOG_FILE);
                    }
                }
                // Now rethrow exception which caused order to fail
                throw $eOrder;
            }
        }

        return $this;
    }

}
