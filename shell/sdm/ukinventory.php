<?php
/**
 * Separation Degrees Media
 *
 * Temporary script to update UK inventory
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
class SDM_UkInventory_Shell extends SDM_Shell_Abstract
{

    const UK_WEBSITE_ID = 3;
    const LOCAL_FTP_PATH = '../../var/ukinventory/ftp-inventory.csv';
    const INVENTORY_DIRECTORY = '../../var/ukinventory';

    /**
     * Sku data loaded in via FTP
     * @var null
     */
    protected $_ftpData = null;

    /**
     * Run script
     */
    public function run()
    {
        ini_set('max_execution_time', 3600);   // 1 hour
        ini_set('memory_limit', '4096M');

        if (!$this->getArg('update') && !$this->getArg('test')) {
            $this->hr();
            $this->out("Please specify either a 'test' or 'update' flag");
            $this->out("Aborting script...");
            $this->hr();
            return;
        }

        $testOnly = $this->getArg('test');

        $this->hr();
        $this->out('Updating UK Inventory...');
        $this->hr();
        if (!$this->getArg('ftp')) {
            $this->out("Checking integrity of ".self::INVENTORY_DIRECTORY);
            mkdir(self::INVENTORY_DIRECTORY, 0777, true);
            $this->hr();
        }
        try {
            if ($this->getArg('ftp')) {
                $this->_importFromFTP();
            }
            $skuData = $this->_getSkuData();
            $this->out("Parsing product information... Please wait...");
            $productArray = array();
            $collection = Mage::getResourceModel('catalog/product_collection')
                ->setStore(SDM_Core_Helper_Data::STORE_CODE_UK_BP)
                ->addAttributeToSelect('*')
                ->addAttributeToFilter( 'sku', array( 'in' => array_keys($skuData) ) );
            foreach($collection as $product) {
                $productArray[$product->getId()] = $product;
            }
            $this->hr();
            $productWebsites = Mage::getResourceModel('catalog/product_website')
                ->getWebsites(array_keys($productArray));
            $total = count($productWebsites);
            $position = 0;
            foreach($productWebsites as $id => $websites) {
                $product = $productArray[$id];
                $sku = $product->getSku();
                $p = "(".round(++$position / $total * 100). "%) ";
                if (in_array(self::UK_WEBSITE_ID, $websites)) {
                    $qty = (int)$skuData[$sku];
                    $stockItem = Mage::getModel('cataloginventory/stock_item')
                        ->loadByProduct($product, self::UK_WEBSITE_ID);
                    $oldQty = (int)$stockItem->getQty();
                    if ($testOnly) {
                        if ($oldQty === $qty) {
                            if ($this->getArg('verbose')) {
                                $this->out("[TEST] >> $p Skipping update of $sku from ".$oldQty." to ".$qty." quantity...");
                            }
                            continue;
                        }
                        $this->out("[TEST] >> $p Updating $sku from ".$oldQty." to ".$qty." quantity...");
                    } else {
                        if ($oldQty === $qty) {
                            if ($this->getArg('verbose')) {
                                $this->out(">> $p Skipping update of $sku from ".$oldQty." to ".$qty." quantity...");
                            }
                            continue;
                        }
                        $this->out(">> $p Updating $sku from ".$oldQty." to ".$qty." quantity...");
                    }
                    if (!$testOnly) {
                        // Update QTY
                        $stockItem->setData('qty', $qty);

                        // Simulate saving from admin
                        $stockItem->setData('store_id', SDM_Core_Helper_Data::STORE_CODE_UK_BP);  
                        $stockItem->setData('website_id', self::UK_WEBSITE_ID);  
                        $stockItem->setData('use_default_website_stock', 0);
                        $stockItem->setCallingClass(
                            'Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogInventoryObserver'
                        );

                        // Save
                        $stockItem->save();
                    }

                    // If old or new is <=0 then run lifecycle...
                    if ($oldQty <= 0 || $qty <= 0) {
                        if (!$testOnly) {
                            $this->out(">> Updating lifecycle for $sku...");
                            Mage::helper('sdm_catalog/lifecycle')->applyLifecycleModifications($product);
                        } else {
                            $this->out("[TEST] >> Updating lifecycle for $sku...");
                        }
                    }
                } else if ($this->getArg('verbose')) {
                    $this->out(">> $p Skipping $sku...");
                }
            }

            $this->_deleteSkuFiles();

        } catch (Exception $e) {
            $this->log->err($e->getMessage());
            $this->log->err('See exception.log for details.');
            Mage::logException($e);
        }
        $this->hr();
    }

    protected function _importFromFTP()
    {
        // define some variables
        $serverFiles = '*.csv';

        // Get config
        $config = Mage::app()->getConfig()->getXPath("global/ukinventory");
        $host = (string)$config[0]->host;
        $user = (string)$config[0]->user;
        $pass = (string)$config[0]->pass;

        // set up basic connection
        $connection = ftp_connect($host);

        // login with username and password
        $loginResult = ftp_login($connection, $user, $pass);
        ftp_pasv($connection, true);

        $this->out("Connecting to FTP server...");
        if ($connection && $loginResult) {
            $this->out("Connected to FPT server!");
            $this->hr();
            $this->out("Proceeding to delete existing inventory files before importing new data.");
        } else {
            $this->hr();
            $this->out("Error connecting to FTP server; exiting!");
            $this->hr();
            mail(
                "alessandro.bassi@separationdegrees.com", 
                "UK INVENTORY ISSUE", 
                "Unable connect to UK inventory FTP server. Please investigate."
            );
            exit;
        }

        // Clear any local files...
        $this->_deleteSkuFiles();

        $this->hr();

        $this->out("Downloading file list from FTP...");
        $files = ftp_nlist($connection, $serverFiles);
        if (empty($files)) {
            $this->out("No files found on FTP server; exiting script...");
            mail(
                "alessandro.bassi@separationdegrees.com", 
                "UK INVENTORY ISSUE", 
                "Unable to find any inventory files on the FTP server. Please investigate."
            );
            $this->hr();
            exit;
        }
        foreach($files as $file) {
            $this->out(">> Found '".$file."'");
            $selectedFile = $file;
        }
        /*
            DO NOT DELETE REMOTE FILE(S)
            foreach($files as $file) {
                if ($file !== $selectedFile) {
                    if ($this->getArg('test')) {
                        $this->out(">> [TEST] Deleting '".$file."'");
                    } else {
                        $this->out(">> Deleting '".$file."'");
                        ftp_delete($connection, $file);
                    }
                }
            }
        */

        $this->hr();
        $this->out(">> Downloading '".$selectedFile."' locally...");

        ob_start();
        $result = ftp_get($connection, "php://output", $selectedFile, FTP_BINARY);
        $this->_ftpData = ob_get_contents();
        ob_end_clean();

        if ($result) {
            if ($this->getArg('test')) {
                $this->out(">> Successfully downloaded inventory data from FTP...");
            }
        } else {
            $this->out("There was a problem reading the FTP source!");
        }

        // close the connection
        ftp_close($connection);
        
        $this->hr();
    }

    /**
     * Grabs the file and parses out skus to update
     */
    protected function _getSkuData()
    {
        // FTP ROUTE
        if ($this->getArg('ftp')) {
            $path = self::INVENTORY_DIRECTORY."/*.csv";
            $this->out('Parsing SKU data downloaded from FTP...');
            $lines = explode("\n", $this->_ftpData);
            $skuData = array();
            foreach($lines as $line) {
                $lineData = array_map('trim', explode(',', $line));
                $sku = $lineData[0];
                $qty = $lineData[1];
                $skuData[$sku] = (int)$qty;
            }
        // FILE ROUTE
        } else {
            $this->out('Loading csv files in '.self::INVENTORY_DIRECTORY);
            $path = self::INVENTORY_DIRECTORY."/*.csv";
            $skuData = array();
            foreach (glob($path) as $file) {
                $this->hr();
                $this->out('FOUND: '.$file);
                $data = array_map('str_getcsv', file($file));
                if (isset($data[0]) && isset($data[0][0]) && strtolower($data[0][0]) =='sku') {
                    unset($data[0]);
                }
                $this->out("Parsing ".count($data)." rows...");
                foreach($data as $lineData) {
                    if (count($lineData) === 2) {
                        $skuData[trim($lineData[0])] = ((int)$lineData[1] > 0 ? (int)$lineData[1] : 0);
                    }
                }
            }
        }
        $this->hr();
        $this->out(">>> Found ".count($skuData)." skus total...");
        $this->hr();

        return $skuData;
    }

    /**
     * Grabs the file and parses out skus to update
     */
    protected function _deleteSkuFiles()
    {
        if ($this->getArg('ftp')) {
            return false;
        }


        $this->hr();
        $this->out('Loading csv files in '.self::INVENTORY_DIRECTORY);
        $path = self::INVENTORY_DIRECTORY."/*.csv";

        foreach (glob($path) as $file) {
            $this->hr();
            if ($this->getArg('test')) {
                $this->out('[TEST] DELETING: '.$file);
            } else {
                $this->out('DELETING: '.$file);
                unlink($file); 
            }
            
        }
    }

    /**
     * Output string to shell
     */
    public function out($input, $eols = 1)
    {
        $this->log->info($input);
    }

    /**
     * Output string to shell
     */
    public function hr()
    {
        $this->log->info("----------------------------------------");
    }
}

$shell = new SDM_UkInventory_Shell;
$shell->run();
