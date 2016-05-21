<?php
/**
 * AX order export script
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
 * CLI to export orders
 */
class SDM_Ax_ExportOrder_Shell extends SDM_Shell_Abstract
{
    public function run()
    {
        ini_set('max_execution_time', 3600);   // 1 hour
        ini_set('memory_limit', '4096M');

        $this->out('Running order export');

        // Store codes, not website
        $usStores = array(
            SDM_Core_Helper_Data::STORE_CODE_US,
            SDM_Core_Helper_Data::STORE_CODE_ER,
            SDM_Core_Helper_Data::STORE_CODE_EE
        );
        $ukStores = array(
            SDM_Core_Helper_Data::STORE_CODE_UK_BP,
            SDM_Core_Helper_Data::STORE_CODE_UK_EU
        );

        Mage::helper('sdm_ax/order')->exportXml($usStores);
        Mage::helper('sdm_ax/order')->exportXml($ukStores);
    }
}

$shell = new SDM_Ax_ExportOrder_Shell();
$shell->run();
