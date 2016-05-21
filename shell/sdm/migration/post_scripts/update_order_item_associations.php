<?php

require_once(dirname(__FILE__) . '/../abstract_migrate.php');
require_once(dirname(__FILE__) . '/../db.php');

/**
 * Update created_at and updated_at timestamps. updated_at is
 */
class Mage_Shell_UpdateOrderItemAssociation extends SDM_Shell_AbstractMigrate
{
    public function run()
    {
        ini_set('max_execution_time', 86400);   // 1 day
        ini_set('memory_limit', '4096M');

        $dbc = $this->getConn('core_write');
        $skus = array();

        // Get all catalog products and their IDs
        $q1 = "SELECT entity_id, sku FROM catalog_product_entity WHERE sku != '' ORDER BY entity_id ASC";
        $results1 = $dbc->fetchAll($q1);
        foreach ($results1 as $one) {
            $skus[$one['entity_id']] = $one['sku'];
        }
        // Mage::log($skus); die;

        // Bulk update order item table for each product ID
        $i = 1;
        $N = count($skus);
        foreach ($skus as $id => $sku) {
            echo "$i/$N: Updating SKU $sku | $id" . PHP_EOL;
            $q2 = "UPDATE sales_flat_order_item SET product_id = '$id' WHERE sku = '$sku'";
            // Mage::log($q2);
            $dbc->query($q2);
            $i++;
        }
    }
}

$shell = new Mage_Shell_UpdateOrderItemAssociation();
$shell->run();
