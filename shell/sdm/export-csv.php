<?php
/**
 * Separation Degrees Media
 *
 * Temporary script to export CSV data
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

require_once 'abstract.php';

// @codingStandardsIgnoreStart
class SDM_ExportCsv_Shell extends SDM_Shell_Abstract
{
    public $baseFields = array(
        'id',
        'name',
        'sku'
    );

    public function run()
    {
        ini_set('max_execution_time', 14400);
        ini_set('display_errors', 'On');

        // Set file name
        $exportPath = Mage::getBaseDir('var') . '/export';
        $fileName = $exportPath . DS . 'product_export_' . time() . '.csv';

        $options = getopt("s:f:");

        if (!isset($options['s'])) {
            $this->out("-s parameter is required. See -h for more information.");
            die();
        }

        // Parse StoreID and fields
        $storeId = (int)$options['s'];
        $fields = array_map('trim', explode(",", isset($options['f']) ? $options['f'] : ""));
        $fields = array_filter(array_merge($this->baseFields, $fields));

        // Get all products
        $idArray = $this->_getArrayOfProductIds();
        $count = 0;
        $bar = $this->progressBar(count($idArray));
        $csv = array();

        // Check if directory exists
        if (!file_exists($exportPath)) {
            mkdir($exportPath, 0777, true);
        }

        // Open file
        $file = fopen($fileName, "w");
        $this->out('Writing file: ' . $fileName);

        // Add headings
        fputcsv($file, $fields);

        // Process data
        foreach ($idArray as $data) {
            $product = Mage::getModel("catalog/product")
                ->setStoreId($storeId)
                ->load($data["product_id"]);
            $bar->update(++$count, 'Processing #' . $product->getSku());
            if (in_array($storeId, $product->getStoreIds())) {
                $data = array();
                foreach($fields as $field) {
                    if ($field === 'id') {
                        $data[] = $product->getId();
                    } else if ($field === 'status') {
                        $data[] = $product->getStatus() ? "Enabled" : "Disabled";
                    } else if ($field === 'url') {
                        $data[] = "/".$product->getSku()."/".$product->getUrlPath();
                    } else if (strpos($field, "tag_") === 0 || $field === 'compatibility_product_line') {
                        $data[] = $product->getAttributeText($field);
                    } else{
                        $data[] = $product->getData($field);
                    }
                }
                fputcsv($file, $data);
            }
        }

        // Write to file
        fclose($file);
    }

    public function _getArrayOfProductIds()
    {
        $sql = "SELECT `entity_id` AS 'product_id' FROM `catalog_product_entity`";
        $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        return $readConnection->fetchAll($sql);
    }

    // public function _getArrayOfProductWebsites()
    // {
    //     $productWebsites = Mage::getResourceModel('catalog/product_website')
    //         ->getWebsites(array($productId));
    // }

    public function usageHelp()
    {
        return <<<USAGE
Usage:  php export-csv.php -s [store_id] -f [fields,to,export,comma,separated]

    Output is dumped to /var/export-csv

  -h            Short alias for help
  help          This help
USAGE;
    }

}

$shell = new SDM_ExportCsv_Shell;
$shell->run();
