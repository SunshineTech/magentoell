<?php

require_once(dirname(__FILE__) . '/../abstract_migrate.php');
require_once(dirname(__FILE__) . '/../db.php');
require_once(dirname(__FILE__) . '/../lib/customer.php');

/**
 * Update billing and shipping addresses with the actual ones from the Ellison
 * orders. This was skipped previously to increase speed of the migration.
 *
 * Note that it is assumed the ID range of migrated orders are known prior.
 */
class Mage_Shell_UpdateOrderAddresses extends SDM_Shell_AbstractMigrate
{
    protected $_logFile = 'order_address_update.log';

    public function run()
    {
        $this->log('************************************************');
        $this->log('Starting to update migrated orders\'s addresses *');
        ini_set('max_execution_time', 186400);   // Many days
        ini_set('memory_limit', '4096M');

        $this->_initMongoDb();
        $this->_initCustomerVars();

        $dbc = $this->getConn();
        $orderIds = array();

        // Get all of the migrated orders
        $q = "SELECT entity_id FROM sales_flat_order
            WHERE entity_id >= 1 AND entity_id <= 9999999
                -- AND entity_id = 125
            ORDER BY entity_id ASC";

        $results = $dbc->fetchAll($q);
        foreach ($results as $one) {
            $orderIds[] = $one['entity_id'];
        }
        // print_r($orderIds); die;

        // Update address as necessary
        $i = 1;
        $N = count($orderIds);
        $bar = $this->progressBar($N);
        foreach ($orderIds as $id) {
            // Load Magento order
            $order = Mage::getModel('sales/order')->load($id);

            // Load Ellison order
            $ellisonOrder = $this->_getEllisonOrder($order->getIncrementId());
            if (!$ellisonOrder) {
                $this->log("$i/$N: [Fail] Ellison order {$order->getIncrementId()} was not found");
                continue;
            }

            // Extract addresses
            $billing = new stdClass;
            $shipping = new stdClass;

            $billing->type = 'billing';
            $billing->country = $ellisonOrder->payment['country'];
            $billing->first_name = $ellisonOrder->payment['first_name'];
            $billing->last_name = $ellisonOrder->payment['last_name'];
            $billing->address1 = $ellisonOrder->payment['address1'];
            $billing->address2 = $ellisonOrder->payment['address2'];
            $billing->city = $ellisonOrder->payment['city'];
            $billing->state = $ellisonOrder->payment['state'];
            $billing->zip = $ellisonOrder->payment['zip_code'];
            $billing->phone = $ellisonOrder->payment['phone'];
            $billing->email = $ellisonOrder->payment['email'];
            if (isset($ellisonOrder->payment['company'])) {
                $billing->company = $ellisonOrder->payment['company'];
            } else {
                $billing->company = '';
            }

            $shipping->type = 'shipping';
            $shipping->country = $ellisonOrder->address['country'];
            $shipping->first_name = $ellisonOrder->address['first_name'];
            $shipping->last_name = $ellisonOrder->address['last_name'];
            $shipping->address1 = $ellisonOrder->address['address1'];
            $shipping->address2 = $ellisonOrder->address['address2'];
            $shipping->city = $ellisonOrder->address['city'];
            $shipping->state = $ellisonOrder->address['state'];
            $shipping->zip = $ellisonOrder->address['zip_code'];
            $shipping->phone = $ellisonOrder->address['phone'];
            $shipping->email = $ellisonOrder->address['email'];
            if (isset($ellisonOrder->address['company'])) {
                $shipping->company = $ellisonOrder->address['company'];
            } else {
                $shipping->company = '';
            }

            $ellisonAddresses = array();
            $ellisonAddresses[0] = $billing;
            $ellisonAddresses[1] = $shipping;

            // Validate and clean it to be suitable for Magento
            $ellisonAddresses = validateAddresses(
                $ellisonAddresses,
                $this->_countryMapping,
                $this->_stateMapping,
                $this->_regionMapping,
                null,   // Website ID irrelevant
                true    // Skip check...
            );
            $billing = $ellisonAddresses[0];    // Put them back
            $shipping = $ellisonAddresses[1];
            // $this->log($ellisonAddresses);

            // Update Magento order's addresses if it hasn't been
            $message1 = $this->_updateAddress(
                $order,
                $billing,
                'billing'
            );
            if ($message1 === true) {
                // $this->log("$i/$N: [Success Billing] Order #{$order->getIncrementId()}", null, null, false);
            } else {
                // $this->log("$i/$N: [Fail Billing] Order #{$order->getIncrementId()}: $message1", null, null, false);
            }

            $message2 = $this->_updateAddress(
                $order,
                $shipping,
                'shipping'
            );
            if ($message2 === true) {
                // $this->log("$i/$N: [Success Shipping] Order #{$order->getIncrementId()}", null, null, false);
            } else {
                // $this->log("$i/$N: [Fail Shipping] Order #{$order->getIncrementId()}: $message2", null, null, false);
            }

            $bar->update($i);
            $i++;
            // break;
        }

        $this->log('Script finished ********************************');
        $this->log('************************************************');
        $this->log('');
    }

    /**
     * Updates the sales/order_address object
     *
     * @param Mage_Sales_Model_Order $order
     * @param stdClass               $ellisonAddress
     * @param str                    $type
     *
     * @return str|bool
     */
    protected function _updateAddress($order, $address, $type)
    {
        $salesAddress = $order->{'get' . ucwords($type) . 'Address'}();
        if (!$salesAddress) {
            return "'$type' address not found";
        }

        // Check if it needs to be udpated
        if ($this->_isAddressTheSame($salesAddress, $address)) {
            return "Same $type address";
        }

        $street1 = trim($address->address1);
        $street2 = trim($address->address2);
        $street = $street1 . ' ' . $street2;
        $salesAddress->setStoreId($order->getStoreId())
            ->setAddressType(strtolower($type))
            ->setCustomerId($order->getCustomerId())
            ->setFirstname($address->first_name)
            ->setLastname($address->last_name)
            ->setCompany($address->company)
            ->setStreet($street)
            ->setCity($address->city)
            ->setPostcode($address->zip)
            ->setCountryId($address->country)
            ->setTelephone($address->phone)
            ->setEmail($address->email)
            ->setRegion($address->state)
            ->setCountryId($address->country)
            ->setRegionId($address->regionId)
            ->setCustomerAddressId(null)    // Does not need to be associated
            ->setPrefix(null)
            ->setMiddlename(null)
            ->setSuffix(null)
            ->setFax(null)
            ->save();

        // print_r($salesAddress->debug());
        return true;
    }

    /**
     * Compare Magento's and Ellison's addresses
     *
     * @param Mage_Sales_Model_Order_Address $mAddress
     * @param stdClass                       $eAddress
     *
     * @return bool
     */
    protected function _isAddressTheSame($mAddress, $eAddress)
    {
        $mStreet = trim($mAddress->getStreet1());
        $eStreet = trim($eAddress->address1);
        // $this->log($mStreet);
        // $this->log($eStreet);

        if (strpos($mStreet, $eStreet) === 0) {
            return true;
        }

        return false;
    }

    /**
     * Returns the Ellison order record
     *
     * @param str $orderNumber Ellison order number
     *
     * @return stdClass|bool
     */
    protected function _getEllisonOrder($orderNumber)
    {
        if (!$orderNumber) {
            return false;
        }

        $q = "SELECT * FROM `orders` WHERE order_number = '$orderNumber' LIMIT 1";
        $order = $this->query($q);
        $order = reset($order);
        if (!$order) {
            return false;
        }

        // Need to decode-unserialize some data
        if ($order->payment) {
            @$order->payment = unserialize(base64_decode($order->payment));
        }
        if ($order->address) {
            @$order->address = unserialize(base64_decode($order->address));
        }
        if ($order->order_items) {
            @$order->order_items = unserialize(base64_decode($order->order_items));
        }

        return $order;
    }
}

$shell = new Mage_Shell_UpdateOrderAddresses();
$shell->run();
