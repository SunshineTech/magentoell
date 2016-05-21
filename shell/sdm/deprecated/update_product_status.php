<?php

require_once 'abstract.php';

class SDM_Migration_Shell_DeleteProducts extends Mage_Shell_Abstract
{
    public function run()
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect(array('id','sku'));
        $this->out('Found products #' . $collection->count());
        $N = $collection->count();
        $i = 0;
        $status = 2;    // 2 for disabled

        foreach ($collection as $one) {
            $i++;
            $this->out("$i/$N: Updating status product ID " . $one->getId() . ' | ' . $one->getSku());

            Mage::getModel('catalog/product')->loadByAttribute('entity_id', $one->getId())
                ->setStatus($status)
                ->save();
        }
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
