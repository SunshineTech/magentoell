<?php

require_once(dirname(__FILE__) . '/../migrate_products.php');
require_once(dirname(__FILE__) . '/../db.php');

/**
 * Update all products' taxonomy
 */
class Mage_Shell_UpdateProductTaxonomy extends SDM_Shell_MigrateProducts
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

        // Get some data ready
        $this->deleteAllFiles('log');
        $this->_initMongoDb();
        $this->_initVars();
        $this->_initProductLineConsolidationMapping();
        $this->_initCompatibilityProductLineMapping();
        $this->setArgs();
        // print_r($this->_paginationSteps);

        // Start the update process
        // Note: each of these methods exists the scripts after its done.
        //       Must re-start it for each product type.
        $this->_createIdeas();
        $this->_createProducts();
    }

    /**
     * Rewritten to skip Magento product update
     */
    protected function _updateMagentoProduct($data, $type, $progress = '')
    {
        $type = 'product';
        if (isset($data->item_num)) {
            $sku = $data->item_num;
        } else {
            $sku = $data->idea_num;
        }

        $product = $this->_getMagentoProduct($sku, $type);

        if ($product && $product->getId()) {
            $this->_addTaxonomy($product, $data); // Now global attribute
            $product->save();
            $this->out("--> {$this->_count}: Updated taxonomy for SKU $sku");
            $this->_count++;
        } else {
            $this->out("Skipping SKU $sku");
        }
    }

    /**
     * Returns am empty product object or an existing one, if available
     */
    protected function _getMagentoProduct($sku, $type)
    {
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

        return $product;
    }

    public function setArgs()
    {
        parent::setArgs();
        $this->_paginationSteps['ideas'][0] = array('page' => 0, 'size' => 9999999);
        $this->_paginationSteps['products'][0] = array('page' => 0, 'size' => 9999999);
    }
}

$shell = new Mage_Shell_UpdateProductTaxonomy();
$shell->run();
