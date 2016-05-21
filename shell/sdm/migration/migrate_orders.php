<?php
/**
 * Order migration script
 *
 * @category  SDM
 * @package   SDM_Shell
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

require_once(dirname(__FILE__) . '/abstract_migrate.php');
require_once(dirname(__FILE__) . '/db.php');
require_once(dirname(__FILE__) . '/lib/customer.php');
require_once(dirname(__FILE__) . '/lib/orderGenerator.php');

class SDM_Shell_MigrateOrders extends SDM_Shell_AbstractMigrate
{
    const MIGRATED_CODE = '1';
    const NOT_MIGRATED_CODE = '0';

    protected $_logFile = 'order_migration.log';
    protected $_refTable = 'temp_order_migration_reference';
    protected $_orderLimit = 1000;  // Run a batch at a time

    protected $_institutionCodeMappings = array(
        'DA' => 'Day Care 3-6 yrs & Afterschool and Summer School',
        'DM' => 'District Media Center HE Head Start - Even Start',
        'IN' => 'Individuals, Teachers, Crafters, or Designers',
        'NP' => 'Non-Profit Organisation Hospitals',
        'PL' => 'Public Library SC School - Church',
        'SD' => 'School - District',
        'SE' => 'School - Elementary SG School - Government, Government Agencies',
        'SH' => 'School - High School',
        'SJ' => 'School - Junior High',
        'SP' => 'School - Pre-School, Early Childhood Centers',
        'PR' => 'School - Private SCHE School Charter Elementary, Jr High, High'
    );

    protected $_includeStatuses = array(
        // Always migrate Shipped orders as they as finalized
        "'Shipped'",

        // In Process orders must be able to be updated with the AX integration.
        // The others below must be migrated only right before launch
        "'In Process'",  // This must be done ONLY RIGHT BEFORE LAUNCH.
        "'New'",
        "'Open'",
        "'Pending'",
        "'Processing'"

        // Below orders are not migrated
        // "'Cancelled'",
        // "'Refunded'",
    );

    protected $_systemCodeToWebsiteId = array(
        'szus' => 1,
        'eeus' => 5,
        'erus' => 4,
        'szuk' => 3,
    );
    protected $_systemCodes = array(
        'szus' => 1,
        'eeus' => 6,
        'erus' => 5,
    );
    protected $_ukSystemCodes = array(
        'en-UK' => 7,
        'en-EU' => 4,
    );

    public function run()
    {
        ini_set('max_execution_time', 186400);   // 1 day
        ini_set('display_errors', 1);
        ini_set('memory_limit', '10240M');

        // Initialize some variables and data
        $this->deleteAllFiles('log');
        $this->_initMongoDb();
        $this->_initCustomerVars();
        $this->_cleanupOrders(); // Cleans up non-valid website orders

        // $this->syncOrders(); // Can be commented out to quicken test runs once synced

        if (0) {
            $this->_resetMigrationFlags();  // This is just for testing
        }

        // Fetch a chunk of orders at a time and migrate them
        while ($mongoIds = $this->getOrdersToProcess()) {

            foreach ($mongoIds as $one) {
                // Get Ellison order record
                $ellisonOrder = $this->_getEllisonOrder($one->mongoid);
                // print_r($ellisonOrder);

                if (!$ellisonOrder) {
                    $this->log("Ellison MongoDB order ID {$one->mongoid} not found in MongoDB");
                    continue;
                }

                // Load customer
                try {
                    $customer = $this->_getCustomer($ellisonOrder);
                    // print_r($customer->debug()); die;
                } catch (Exception $e) {
                    $this->log(
                        "Ellison customer could found order ID {$ellisonOrder->mongoid}."
                            . " Error: {$e->getMessage()}"
                    );

                    $this->updateMigrationStatus($one->mongoid, -1);   // -1 denotes it doesn't need to be migrated
                    continue;
                }

                // Create Magento order
                if ($customer && $customer->getId()) {
                    try {
                        $this->_createOrder($ellisonOrder, $customer);
                    } catch (Exception $e) {
                        $this->log(
                            "Unable to create Ellison order ID {$ellisonOrder->order_number} "
                                . "Error: {$e->getMessage()}"
                        );
                    }

                } else {
                    $this->log(
                        "Customer could not be loaded properly: $email | {$ellisonOrder->system} | order ID {$order->id}"
                    );
                }
                // exit; // Debugging only
            }
        }
    }

    /**
     * Creates an order associated with the given customer
     *
     * @param stdClass $data
     * @param Mage_Customer_Model_Customr $customer
     *
     * @return null
     */
    protected function _createOrder($data, $customer)
    {
        $order = Mage::getModel('sales/order')
            ->loadByAttribute('increment_id', $data->order_number);

        if ($order->getEntityId()) {
            $this->log("Ellison order #{$data->order_number} already exists");
            $this->updateMigrationStatus($data->mongoid, 1);    // Not required but do it anyway
            return;
        }

        $products = array();
        $generator = new OrderGenerator;

        // Prepare products
        if ($data->order_items) {   // If encoding and serialization weren't done correctly, this would be invalid
            $items = $data->order_items;
            $i = 0;
            foreach ($items as $item) {
                $products[$i] = array(
                    'sku' => $item['item_num'],
                    'name' => $item['name'],
                    'price' => $item['sale_price'],
                    'qty' => $item['quantity'],
                );

                if ($data->system == 'szuk') {
                    if ($item['vat_exempt']) {
                        $products[$i]['tax_amount'] = 0;
                        $products[$i]['tax_percent'] = 0;
                    } else {
                        $products[$i]['tax_amount'] = $item['vat'];
                        $products[$i]['tax_percent'] = $item['vat_percentage'];
                    }
                } else {
                    // Order item level tax amount or general percentage not available for non-UK orders
                }
                $i++;
            }
        } else {
            $this->log("Missing order items for #$data->order_number}");
        }

        // Prepare and create order
        $generator->setCustomer($customer);
        $generator->createOrder($products, $data);
        $this->updateMigrationStatus($data->mongoid, 1);
        $this->out('Migrated order #' . $data->order_number . ' | ' . $data->system);
        // echo 'Migrated order #' . $data->order_number . PHP_EOL; die;
    }

    /**
     * Retrieves the customer. If not available, create it.
     *
     * @param stdClass $order Order object from Ellison MondoDB
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer($order)
    {
        $systemCode = $order->system;
        if (isset($this->_systemCodeToWebsiteId[$systemCode])) {
            $websiteId = $this->_systemCodeToWebsiteId[$systemCode];
        } else {
            $this->log("Unable to find website ID for $systemCode | order ID {$order->id}");
            return;
        }

        // If customer already exists, return it
        $email = $order->address['email'];
        $customer = Mage::getModel('customer/customer')->setWebsiteId($websiteId)
            ->loadByEmail($email);

        // No need to update address if customer already exists. Address will be
        // updated post-launch.
        if ($customer->getId()) {
            return $customer;
        }

        // Create the customer, if not avaialble yet
        $customerData = getEllisonCustomer($order->user_id, $this->_dbc);
        $customerData = $this->_cleanUpCustomerData($customerData);
        // print_r($order->user_id); var_dump($customerData);

        $customer = updateCustomer(
            $customerData,
            $websiteId,
            $this->_institutionCodeMappings
        );  // Does not create addresses
        // echo "New customer: " . $customer->getEmail() . ' | Order #' . $order->order_number . PHP_EOL;

        /**
         * Note: This takes too long and creates too many addresses for customers.
         *       Skip this for migration and write a post-launch script to fix
         *       all order addresses.
         */
        // $this->_updateAddresses($customer, $order); // Update addresses before returning
        // $customer = Mage::getModel('customer/customer')->load($customer->getId());  // Reload customer

        return $customer;
    }

    /**
     * Updates the addresses for this customer
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param stdClass$order
     *
     * @return null
     */
    protected function _updateAddresses($customer, $order)
    {
        if (!isset($this->_systemCodeToWebsiteId[$order->system])) {
            return;
        }

        $websiteId = Mage::getModel('core/store')
            ->load($this->_systemCodeToWebsiteId[$order->system])
            ->getWebsiteId();
        $email = $order->address['email'];

        // Addresses should be loaded from the order itself, not from the customer account
        // $temp = getEllisonAddresses($email, $order->system, $this->_dbc);    // Addresses from customer
        $ellisonAddresses = array();
        $billing = new stdClass;
        $shipping = new stdClass;

        $billing->type = 'billing';
        $billing->country = $order->payment['country'];
        $billing->first_name = $order->payment['first_name'];
        $billing->last_name = $order->payment['last_name'];
        $billing->address1 = $order->payment['address1'];
        $billing->address2 = $order->payment['address2'];
        $billing->city = $order->payment['city'];
        $billing->state = $order->payment['state'];
        $billing->zip = $order->payment['zip_code'];
        $billing->phone = $order->payment['phone'];
        $billing->email = $order->payment['email'];
        if (isset($order->payment['company'])) {
            $billing->company = $order->payment['company'];
        } else {
            $billing->company = '';
        }

        $shipping->type = 'shipping';
        $shipping->country = $order->address['country'];
        $shipping->first_name = $order->address['first_name'];
        $shipping->last_name = $order->address['last_name'];
        $shipping->address1 = $order->address['address1'];
        $shipping->address2 = $order->address['address2'];
        $shipping->city = $order->address['city'];
        $shipping->state = $order->address['state'];
        $shipping->zip = $order->address['zip_code'];
        $shipping->phone = $order->address['phone'];
        $shipping->email = $order->address['email'];
        if (isset($order->address['company'])) {
            $shipping->company = $order->address['company'];
        } else {
            $shipping->company = '';
        }

        $ellisonAddresses[0] = $billing;
        $ellisonAddresses[1] = $shipping;
        $ellisonAddresses = validateAddresses(
            $ellisonAddresses,
            $this->_countryMapping,
            $this->_stateMapping,
            $this->_regionMapping,
            $websiteId,
            true    // Skip check...
        );

        // print_r($order);
        // print_r($temp);

        updateAddresses($ellisonAddresses, $customer->getId(), true);
        // print_r($ellisonAddresses);
    }

    /**
     * Returns the Ellison order record
     *
     * @param str $id MongoDB ID
     *
     * @return stdClass|bool
     */
    protected function _getEllisonOrder($id)
    {
        if (!$id) {
            return false;
        }

        $q = "SELECT * FROM `orders` WHERE mongoid = '$id' LIMIT 1";
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

    /**
     * Returns a chunk of MongoDB IDs of orders not migrated yet.
     *
     * @return array|bool Array of stdClass or false
     */
    public function getOrdersToProcess()
    {
        $q = "SELECT mongoid FROM `{$this->_refTable}`
            WHERE migrated = 0
            ORDER BY id ASC
            LIMIT {$this->_orderLimit}";
        $mongoIds = $this->query($q);
        // print_r($mongoIds);

        if (count($mongoIds) > 0) {
            return $mongoIds;
        } else {
            return false;
        }
    }

    /**
     * Populates the order migration table
     */
    public function syncOrders()
    {
        $this->log('Syncing order reference table', null, null, true);
        $this->_prepareOrderSyncTable();

        $statusToSelect = implode(',', $this->_includeStatuses);

        // All order: "SELECT mongoid FROM orders GROUP BY mongoid ORDER BY id ASC"
        $allOrderMongoIds = $this->query(
            "SELECT mongoid,`status` FROM orders
            WHERE `status` IN ($statusToSelect)
            GROUP BY mongoid ORDER BY id ASC"
        );
        // print_r($allOrderMongoIds);

        // Insert new records
        foreach ($allOrderMongoIds as $order) {
            // $preQ = "SELECT id FROM {$this->_refTable} WHERE mongoid = '{$order->mongoid}'";
            // $result = $this->query($preQ);
            // if (!$result) {
            //     $q = "INSERT INTO `{$this->_refTable}` (`mongoid`, `migrated`)
            //         VALUES ('{$order->mongoid}','0')";
            //     $this->query($q);
            // }

            //
            $q = "INSERT IGNORE INTO `{$this->_refTable}` (`mongoid`, `migrated`)
                    VALUES ('{$order->mongoid}','0')";
            $this->query($q);
        }
    }

    /**
     * Marks all orders as not migrated.
     *
     * For testing only.
     */
    public function _resetMigrationFlags()
    {
        $this->query("UPDATE {$this->_refTable} SET migrated = 0");
    }

    /**
     * Creates the order migration reference table used to flag which orders
     * have been migrated
     */
    protected function _prepareOrderSyncTable()
    {
        $this->query("
            CREATE TABLE IF NOT EXISTS `{$this->_refTable}` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `mongoid` varchar(255) DEFAULT NULL,
                `migrated` int(1) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                UNIQUE KEY `mongoid` (`mongoid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Remove up unnecessary Ellison orders
     */
    protected function _cleanupOrders()
    {
        // Remove all non szus,eeus,szuk,erus orders.
        // This take 30 seconds on local.
        $this->query(
            "DELETE FROM orders
            WHERE system != 'szus' AND system != 'eeus'
                AND system != 'szuk' AND system != 'erus'"
        );
    }

    /**
     * Clean up Ellison customer data to be more suitable for Magento
     */
    protected function _cleanUpCustomerData($data)
    {
        // Fix name
// if ($data || !$data->name) {
//     print_r($data); die;
// }
        $bow = explode(' ', $data->name);
        $data->firstname = array_shift($bow);
        $data->lastname = implode(' ', $bow);
        $data->websites = array();
        $data->email = mysql_real_escape_string($data->email);
        // print_r($data->email); die;

        // Fix ERP
        $data->erp = trim($data->erp);
        if (strtolower($data->erp) === 'new') {
            $data->erp = '';
        }

        // Convert website assignment
        $systems = explode('|', trim($data->systems_enabled));
        foreach ($systems as $code) {
            if ($code === 'eeuk') { // Store no longer exists
                continue;
            }
            if (isset($this->_magentoWebsites[$this->_websiteMapping[$code]])) {
                $data->websites[$this->_magentoWebsites[$this->_websiteMapping[$code]]]
                    = $this->_magentoWebsites[$this->_websiteMapping[$code]];
            }
        }

        // Assign proper customer group
        if (isset($this->_magentoCustomerGroupMapping[$data->discount_level]) && $data->discount_level != 0){
            $data->customer_group_id = $this->_magentoCustomerGroupMapping[$data->discount_level];
        // Otherwise, it's just a regulat customer
        } else {
            $data->customer_group_id = 1;   // "General" Magento cutomer group
        }
        // $this->out($data->customer_group_id); die;

        return $data;
    }

    /**
     * Update the order migration reference table record with the given flag
     * value
     *
     * @param str $id MongoDB
     * @param bool|int $flag 1 or 0
     *
     * @return
     */
    public function updateMigrationStatus($id, $flag)
    {
        $flag = (int)$flag;
        $this->query(
            "UPDATE {$this->_refTable} SET migrated = '$flag'
            WHERE mongoid = '$id'"
        );
    }
}

$shell = new SDM_Shell_MigrateOrders();
$shell->run();