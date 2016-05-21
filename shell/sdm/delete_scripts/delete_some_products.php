<?php

require_once(dirname(__FILE__) . '/../abstract.php');
/**
 * DELETE
 * FROM catalog_product_entity
 * WHERE sku NOT IN ('12026','659766','659521','12025','659766','14843','658236','18064');
 */
class SDM_Migration_Shell_DeleteProducts extends SDM_Shell_Abstract
{
    public function run()
    {
        ini_set('max_execution_time', 86400);   // 1 day
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
        $saveIds = array(
            // Dies
            '12026',            // Idea
            '659766', '659521', // Above idea's products
            '12025',
            '659766',
            '14843',
            '658236',
            // Machine(s)
            '18064'
        );

        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect(array('id','sku'));
        // $this->out($collection->getSelect()->__toString());

        if (!empty($saveIds)) {
            $collection->addAttributeToFilter(
                'sku',
                array('nin' => $saveIds)
            );
        }

        $this->out('Found products #: ' . $collection->count());
        $this->out('>> Deleting SKUs but saving these: ' . implode(',', $saveIds));
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
