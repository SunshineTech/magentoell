<?php
/**
 * Separation Degrees Media
 *
 * Refresh product lifecycle
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Catalog
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

require_once 'abstract.php';

// @codingStandardsIgnoreStart
class SDM_LifecycleRefresh_Shell extends SDM_Shell_Abstract
{
    /**
     * Run script
     */
    public function run()
    {
        // Production has no limits in CLI
        if (ini_get('max_execution_time') !== '0') {
            ini_set('max_execution_time', 3600);
        }
        if (ini_get('memory_limit') !== '-1') {
            ini_set('memory_limit', '4096M');
        }

        if (!$this->getArg('updateall')) {
            echo $this->usageHelp();
            return;
        }

        $this->log->info('Updating All Lifecycle...');
        try {
            $sql = "SELECT `entity_id` AS 'product_id' FROM `catalog_product_entity`";
            $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
            $idArray = $readConnection->fetchAll($sql);
            $count = 0;
            $bar = $this->progressBar(count($idArray));
            foreach ($idArray as $data) {
                $bar->update(++$count, 'Updating ' . $data["product_id"]);
                Mage::helper('sdm_catalog/lifecycle')
                    ->applyLifecycleModifications($data["product_id"]);
            }
        } catch (Exception $e) {
            $this->log->err($e->getMessage());
            $this->log->err('See exception.log for details.');
            Mage::logException($e);
        }
        $bar->finish();
    }

    /**
     * Retrieve Usage Help Message
     *
     * @return string
     */
    public function usageHelp()
    {
        return <<<USAGE

Usage:

  php -f shell/sdm/lifecycle-refresh.php -- [options]

Options:

  updateall                  Update lifecycle on all products

  help                       This help


USAGE;
    }
}

$shell = new SDM_LifecycleRefresh_Shell;
$shell->run();
