<?php

require_once(dirname(__FILE__) . '/../abstract_migrate.php');
require_once(dirname(__FILE__) . '/../db.php');

/**
 * Update the passwords for migrated customer
 */
class Mage_Shell_UpdateImportedCustomerPassword extends SDM_Shell_AbstractMigrate
{
    const IMPORTED_PASSWORD = 'imported_customer';

    public function run()
    {
        ini_set('max_execution_time', 86400);   // 1 day
        ini_set('memory_limit', '4096M');

        $dbc = $this->getConn();
        $customerIds = array();

        // Get all catalog products and their IDs
        $q = "SELECT entity_id FROM customer_entity ORDER BY entity_id ASC";
        $results = $dbc->fetchAll($q);
        foreach ($results as $one) {
            $customerIds[] = $one['entity_id'];
        }
        // Mage::log($customerIds); die;

        // Bulk update order item table for each product ID
        $i = 1;
        $N = count($customerIds);
        foreach ($customerIds as $id) {
            $customer = Mage::getModel('customer/customer')->load($id);

            if ($customer->getId()) {
                echo "$i/$N: Updating customer {$customer->getEmail()} | $id" . PHP_EOL;
                $customer->setPasswordHash(self::IMPORTED_PASSWORD);
            } else {
                echo "$i/$N: Customer {$customer->getEmail()} | $id not found" . PHP_EOL;
            }

            try {
                $customer->save();
            } catch (Exception $e) {
                echo "$i/$N: Failed to save customer {$customer->getEmail()}" . PHP_EOL;
            }

            $i++;
        }
    }
}

$shell = new Mage_Shell_UpdateImportedCustomerPassword();
$shell->run();
