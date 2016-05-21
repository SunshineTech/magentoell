<?php

require_once(dirname(__FILE__) . '/../migrate_products.php');
require_once(dirname(__FILE__) . '/../db.php');

/**
 * Update all products global scope special prices
 */
class Mage_Shell_UpdateAllProduct extends SDM_Shell_MigrateProducts
{
    protected $_count = 1;

    protected $_alwaysUpdate = true;

    public function __destruct()
    {
        // Required due to logging error
    }

    public function run()
    {
        ini_set('max_execution_time', 186400);  // Many days
        ini_set('memory_limit', '5000M');

        $dbc = $this->getConn('core_write');
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('id')
            ->addAttributeToFilter('type_id', 'simple');

        // Insert special_price = null for store_if = 0, if one doesn't exist
        $i = 0;
        foreach ($collection as $one) {
            $productId = $one->getId();
            $q = "INSERT IGNORE `catalog_product_entity_decimal`
                SET `entity_type_id` = 4, `attribute_id` = 76, `store_id` = 0, `entity_id` = $productId, `value` = NULL";
            $dbc->query($q);

            $this->out("$i: SKU {$one->getSku()} - updated special_price");
            $i++;
        }
    }
}

$shell = new Mage_Shell_UpdateAllProduct();
$shell->run();
