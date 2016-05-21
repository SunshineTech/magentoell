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
if ($version == '1.0.19') {
    return;
} elseif ($version != '1.0.18') {
    die('Please, run migration Helpdesk 1.0.18');
}
$installer->startSetup();
if (Mage::registry('mst_allow_drop_tables')) {
    $sql = '
    ';
    $installer->run($sql);
}
$sql = "
ALTER TABLE  {$this->getTable('helpdesk/gateway')}  ADD  FOREIGN KEY (`department_id`) REFERENCES `{$this->getTable('helpdesk/department')}` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE
";
$installer->run($sql);

/*                                    **/

$installer->endSetup();
