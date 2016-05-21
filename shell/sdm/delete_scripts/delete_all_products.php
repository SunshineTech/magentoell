<?php

require_once(dirname(__FILE__) . '/../abstract.php');

class SDM_Migration_Shell_DeleteProducts extends SDM_Shell_Abstract
{
    public function run()
    {
        ini_set('max_execution_time', 14400);
        ini_set('display_errors', 'On');
        ini_set('memory_limit', '4096M');

        $type = $this->getArg('m');
        if ($type !== 'all') {
            $this->out("Warning: Must supply argument '-m all' to delete all products. Exiting...");
            exit;
        }

        $storeId = 1;
        Mage::app()->setCurrentStore(0);

        // Adjust this accordingly

        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect(array('id','sku'));
        // $this->out($collection->getSelect()->__toString());

        $this->out('Found products #: ' . $collection->count());
        $N = $collection->count();
        $i = 0;

        foreach ($collection as $product) {
            $i++;
            $this->out("$i/$N: Deleting product ID " . $product->getId() . ' | ' . $product->getSku());
            $product->delete();
        }
    }
}

$shell = new SDM_Migration_Shell_DeleteProducts();
$shell->run();
