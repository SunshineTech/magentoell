<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Help Desk MX
 * @version   1.1.3
 * @build     1578
 * @copyright Copyright (C) 2015 Mirasvit (http://mirasvit.com/)
 */



/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$version = Mage::helper('mstcore/version')->getModuleVersionFromDb('mst_helpdesk');
if ($version == '1.0.13') {
    return;
} elseif ($version != '1.0.12') {
    die('Please, run migration Helpdesk 1.0.12');
}
$installer->startSetup();
$sql = "
ALTER TABLE `{$this->getTable('helpdesk/ticket')}` ADD COLUMN `search_index` TEXT;
";
$installer->run($sql);

/*                                    **/

$installer->endSetup();
