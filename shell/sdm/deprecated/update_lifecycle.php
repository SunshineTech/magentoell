<?php
/**
 * IMPORTANT:
 *
 * Indexing must be set to Update on Save.
 *
 */
require_once(dirname(__FILE__) . '/abstract.php');

class SDM_Migration_Shell_DeleteProducts extends SDM_Shell_Abstract
{
    protected $_pageNum = null;
    protected $_pageSize = null;
    protected $_breakAfterOnePage = false;

    public function run()
    {
        ini_set('max_execution_time', 86400);   // 1 day
        ini_set('display_errors', 'On');
        ini_set('memory_limit', '4096M');
        $this->_init();

        $this->setArgs();

        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect(array('id','sku'))
            ->setOrder('entity_id', 'asc');

        if ($this->_pageSize && $this->_pageNum) {
            $collection->setPageSize($this->_pageSize)
                ->setCurPage($this->_pageNum);
        }

        $this->out('Found products #: ' . $collection->count());
        // echo ($collection->getSelect()->__toString()) . PHP_EOL; return;
        $N = $collection->count();
        $i = 0;

        foreach ($collection as $product) {
            $i++;
            Mage::helper('sdm_catalog/lifecycle')
                ->applyLifecycleModifications($product->getId());

            $this->out("$i/$N: Updated LC for product ID " . $product->getId() . ' | ' . $product->getSku());
            $this->out('>> ' . $this->getMemoryUsageNow());
        }

        $this->_end();
    }

   public function setArgs()
    {
        $pageNum = $this->getArg('p');
        $pageSize = $this->getArg('n');
        $break = false;

        // Set page number and page size of the SELECT query
        if ($pageSize && $pageNum) {
            $this->_pageNum = $pageNum;
            $this->_pageSize = $pageSize;
            $this->out('Pagination activated.');
        } elseif ($pageSize) {
            $this->out('You must supply both arguments n and p');
            exit;
        } elseif ($pageNum) {
            $this->out('You must supply both arguments n and p');
            exit;
        }

        if (isset($this->getArg('b')) && (bool)$this->getArg('b') === true && $pageSize && $pageNum) {
            $this->_breakAfterOnePage = true;
        }
    }
}

$shell = new SDM_Migration_Shell_DeleteProducts();
$shell->run();
