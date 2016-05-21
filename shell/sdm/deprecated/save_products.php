<?php

require_once(dirname(__FILE__) . '/abstract.php');

class SDM_Migration_Shell_DeleteProducts extends SDM_Shell_Abstract
{
    public function run()
    {
        ini_set('max_execution_time', 86400);   // 1 day
        ini_set('display_errors', 'On');
        ini_set('memory_limit', '4096M');
        $this->_init();

        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect(array('id','sku'));

        $this->out('Found products #: ' . $collection->count());
        $N = $collection->count();
        $i = 0;

        foreach ($collection as $product) {
            $i++;
            $product = Mage::getModel('catalog/product')->load($product->getId())
                ->save();
            $this->out("$i/$N: Saved product ID " . $product->getId() . ' | ' . $product->getSku());
            $this->out('>> ' . $this->getMemoryUsageNow());
        }

        $this->_end();
    }

    public function out($val)
    {
        if (is_array($val)) {
            print_r($val);
        } else {
            echo $val . PHP_EOL;
        }
    }
}

$shell = new SDM_Migration_Shell_DeleteProducts();
$shell->run();
