<?php

require_once(dirname(__FILE__) . '/abstract_migrate.php');

class SDM_Shell_ImportSavedQuotes extends SDM_Shell_AbstractMigrate
{
    const EEUS_STORE_ID = 6;

    protected $_logFile = 'saved_quote_migration.log';

    public function run()
    {
        $this->deleteAllFiles('log');
        $this->_initMongoDb();

        // Clear all quotes
        $this->out('Removing all saved quotes...');
        $this->_deleteAllSavedQuote();

        // Save test saved quote
        $this->out('Migrating saved quotes...');
        $this->_processQuotes();
    }

    /**
     * Wrapper to migrated saved quotes
     */
    protected function _processQuotes()
    {
        $quotes = $this->_getValidEllisonQuotes();
        $i = 0;
        $N = count($quotes);

        foreach ($quotes as $quote) {
            $this->_createQuote($quote);
            $i++;
            $this->out("$i/$N: {$quote->quote_number} saved");
        }
    }

    /**
     * Creates a saved quote
     *
     * @see SDM_SavedQuote_Helper_Converter::saveQuote
     * @param stdClass $data
     */
    protected function _createQuote($data)
    {
        $customer = $this->_getCustomer($data->user_id);
        if (!$customer || !$customer->getId()) {
            $this->log("Customer {$data->user_id} could not be found in Magento");
            return;
        }

        $savedQuote = Mage::getModel('savedquote/savedquote')
            ->setQuoteId(null)  // Note related quote
            ->setStoreId(self::EEUS_STORE_ID) // EEUS only
            ->setIsActive(SDM_SavedQuote_Helper_Data::ACTIVE_FLAG)  // Only active quotes are being imported
            ->setIncrementId($data->quote_number)
            ->setName($data->name)
            // ->setCreatedAt($data->created_at)   // Re-saved after initial save
            ->setExpiresAt($data->expires_at)
            ->setUpdatedAt($data->updated_at)
            // Customer
            ->setCustomerId($customer->getId())
            ->setCustomerTaxClassId($customer->getTaxClassId())
            ->setCustomerGroupId($customer->getGroupId())
            ->setCustomerEmail($customer->getEmail())
            ->setCustomerPrefix(null)       // Did not migrate
            ->setCustomerFirstname($customer->getFirstname())
            ->setCustomerMiddlename(null)   // Did not migrate
            ->setCustomerLastname($customer->getLastname())
            ->setCustomerSuffix(null)       // Did not migrate
            ->setCustomerNote(null)         // Did not migrate
            ->setCouponCodes($data->coupon_code)
            // Shipping
            ->setShippingCost($data->shipping_amount)
            ->setShippingCode('sdm_shipping_table_us_standard')
            ->setShippingMethod($data->shipping_service)
            ->setCarrier(null)              // Not used
            ->setCarrierTitle(null)
            // Totals
            ->setTaxAmount($data->tax_amount)
            ->setSubtotal($data->subtotal_amount);

        // Some fields are null when not defined, so follow that convention
        if (isset($data->handling_amount) && $data->handling_amount > 0) {
            $savedQuote->setSdmShippingSurcharge($data->handling_amount);
        }
        if (isset($data->total_discount) && abs($data->total_discount) > 0) {
            $savedQuote->setDiscount(abs($data->total_discount));   // Not used to compute grand total
        }

        $grandTotal = $data->subtotal_amount + $data->tax_amount + $data->shipping_amount
            + (double)$data->handling_amount;
        $savedQuote->setGrandTotal($grandTotal)
            ->save();

        // Need another save to correct created_at
        $savedQuote->setCreatedAt($data->created_at)->save();

        // Add items and address separately
        $result1 = $this->_addItems($savedQuote->getId(), $this->decode($data->order_items));
        $result2 = $this->_addAddresses($savedQuote->getId(), $this->decode($data->address));

        if ($result1 === false || $result2 === false) {
            $this->log("Saved quote could not be saved properly and has been removed.");
            $savedQuote->delete();
        }
        // print_r($customer->debug());
        // print_r($data);
    }

    protected function _addItems($quoteId, $orderItems)
    {
        foreach ($orderItems as $item) {
            // print_r($item);

            // Load the Magento product
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $item['item_num']);
            if (!$product || !$product->getId()) {
                $this->log("Product {$item['item_num']} could not be found");
                return false;
            }

            $savedQuoteItem = Mage::getModel('savedquote/savedquote_item')
                ->setSavedQuoteId($quoteId)
                ->setProductId($product->getId())
                ->setStoreId(self::EEUS_STORE_ID)
                ->setSku($item['item_num'])
                ->setName($item['name'])
                ->setQty($item['quantity'])
                ->setProductType($product->getTypeId())
                ->setPrice($item['sale_price'])
                ->setTaxPercent(null)   // N/A from MongoDB
                ->setTaxAmount(0)       // N/A from MongoDB
                ->setRowTotal($item['sale_price'] * $item['quantity'])
                ->setDiscountAmount($item['discount']);

            try {
                $savedQuoteItem->save();
            } catch (Exception $e) {
                $this->log('Failed to save saved quote item. Error: ' . $e->getMessge());
                return false;
            }
        }

        return true;
    }

    protected function _addAddresses($quoteId, $address)
    {
        $street = trim($address['address1'] . ' ' . $address['address2']);

        // EEUS quotes are all within US
        $region = Mage::getModel('directory/region')->loadByCode($address['state'], 'US');
        if (!$region->getId()) {
            $this->log("Unable to find region for {$address['state']}, {$address['country']}");
            return false;
        }
        $regionId = $region->getId();
        $regionName = $region->getDefaultName();
        $countryId = $region->getCountryId();

        $savedQuoteAddress = Mage::getModel('savedquote/savedquote_address')
            ->setSavedQuoteId($quoteId)
            ->setAddressType(Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING)
            ->setFirstname($address['first_name'])
            ->setLastname($address['last_name'])
            ->setCompany($address['company'])
            ->setStreet($street)
            ->setCity($address['city'])
            ->setRegion($regionName)
            ->setRegionId($regionId)
            ->setPostcode($address['zip_code'])
            ->setCountryId($countryId)
            ->setTelephone($address['phone'])
            ->setSameAsBilling(0);

        $savedQuoteAddress->save();
    }

    /**
     * Retrieves all saved quotes that are still valid
     *
     * @return array
     */
    protected function _getValidEllisonQuotes()
    {
        // Get the cut-off date ignoring the time of day
        $cutOffDate = date('Y-m-d'); // Today

        // $q = "SELECT * FROM quotes WHERE created_at >= '$cutOffDate' AND `system`='eeus' AND `active` = 1";
        $q = "SELECT * FROM quotes WHERE expires_at >= '$cutOffDate' AND `system`='eeus' AND `active` = 1";
        $quotes = $this->query($q);
        // Mage::log($cutOffDate);

        return $quotes;
    }

    protected function _deleteAllSavedQuote()
    {
        $collection = Mage::getModel('savedquote/savedquote')->getCollection()
            ->addFieldToSelect(array('entity_id', 'increment_id'));

        foreach ($collection as $one) {
            $this->out('Entity ID: ' . $one->getId() . ': ' . $one->getIncrementId() . ' --> Deleted');
            $one->delete();
        }
    }

    /**
     * Retrieves the Magento customer given the user_id in the ported MongoDB
     *
     * @param int $userId
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer($userId)
    {
        $email = $this->_getEllisonCustomer($userId);
        $websiteId = 5; // Hard-coded to only process EEUS customers

        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId($websiteId)
            ->loadByEmail($email);

        return $customer;
    }

    protected function _getEllisonCustomer($userId)
    {
        $q = "SELECT * FROM users WHERE mongoid = '$userId'";
        $customer = $this->query($q);
        if (!$customer && $systems_enabled !== 'eeus') {
            return;
        }

        $customer = reset($customer);

        return trim($customer->email);
    }
}

$shell = new SDM_Shell_ImportSavedQuotes();
$shell->run();