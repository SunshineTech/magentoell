<?php
/**
 * AX order update script
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Ax
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

require_once(dirname(__FILE__) . '/../abstract.php');

/**
 * CLI to update orders
 */
class SDM_Ax_UpdateOrder_Shell extends SDM_Shell_Abstract
{
    public function run()
    {
        ini_set('max_execution_time', 3600);   // 1 hour
        ini_set('memory_limit', '4096M');

        $this->out('Running product/inventory import');

        Mage::helper('sdm_ax/order')->processStatusUpdate();
    }
}

$shell = new SDM_Ax_UpdateOrder_Shell();
$shell->run();
