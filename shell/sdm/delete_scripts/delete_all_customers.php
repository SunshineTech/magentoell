<?php

require_once(dirname(__FILE__) . '/../abstract.php');

class SDM_Migration_Shell_DeleteCustomers extends SDM_Shell_Abstract
{
    public function run()
    {
        ini_set('max_execution_time', 14400);
        ini_set('display_errors', 'On');
        ini_set('memory_limit', '4096M');

        $type = $this->getArg('m');
        if ($type !== 'all') {
            $this->out("Warning: Must supply argument '-m all' to delete all customers. Exiting...");
            exit;
        }

        $this->getConn('write_core')->query("TRUNCATE TABLE sfc_cybersource_payment_profile");
        $this->out('`sfc_cybersource_payment_profile` truncated');

        $this->getConn('write_core')->query("
            ALTER TABLE `customer_entity` AUTO_INCREMENT = 1;
            ALTER TABLE `customer_entity_datetime` AUTO_INCREMENT = 1;
            ALTER TABLE `customer_entity_decimal` AUTO_INCREMENT = 1;
            ALTER TABLE `customer_entity_int` AUTO_INCREMENT = 1;
            ALTER TABLE `customer_entity_text` AUTO_INCREMENT = 1;
            ALTER TABLE `customer_entity_varchar` AUTO_INCREMENT = 1;
            ALTER TABLE `customer_address_entity` AUTO_INCREMENT = 1;
            ALTER TABLE `customer_address_entity_datetime` AUTO_INCREMENT = 1;
            ALTER TABLE `customer_address_entity_decimal` AUTO_INCREMENT = 1;
            ALTER TABLE `customer_address_entity_int` AUTO_INCREMENT = 1;
            ALTER TABLE `customer_address_entity_text` AUTO_INCREMENT = 1;
            ALTER TABLE `customer_address_entity_varchar` AUTO_INCREMENT = 1;
        ");
        $this->out('All customer-related tables auto-increment reset to 1');


        // Mage::app()->setCurrentStore(0);
        $loop = true;
        $N = 5000;
        $k = 1;

        // Delete a chunk at a time
        while ($loop) {
            $collection = Mage::getModel('customer/customer')->getCollection()
                ->addAttributeToSelect(array('id','email'))
                // ->addAttributeToFilter('entity_id', array('gt' => 24))
                ; // save Separation Degrees accounts

            $collection->getSelect()->limit($N);
            // $this->out($collection->getSelect()->__toString());

            $this->out("Run #$k: Found customers #: " . $collection->count());
            $N = $collection->count();
            if ($N > 0) {
                $i = 0;
                foreach ($collection as $customer) {
                    $i++;
                    $this->out("$i/$N: Deleting customer ID " . $customer->getId() . ' | ' . $customer->getEmail());
                    $customer->delete();
                }
            } else {
                $loop = false;
            }

            $k++;
        }
    }
}

$shell = new SDM_Migration_Shell_DeleteCustomers();
$shell->run();
