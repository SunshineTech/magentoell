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
if ($version == '1.0.20') {
    return;
} elseif ($version != '1.0.19') {
    die('Please, run migration Helpdesk 1.0.19');
}
$installer->startSetup();
if (Mage::registry('mst_allow_drop_tables')) {
    $sql = "
       DROP TABLE IF EXISTS `{$this->getTable('helpdesk/user')}`;
    ";
    $installer->run($sql);
}
$sql = "
CREATE TABLE IF NOT EXISTS `{$this->getTable('helpdesk/user')}` (
    `user_id` ".(Mage::getVersion() >= '1.6.0.0' ? 'int(10)' : 'mediumint(11)')." unsigned,
    `signature` TEXT,
    KEY `fk_helpdesk_user_user_id` (`user_id`),
    CONSTRAINT `mst_7c94023426a7e3597600f6edff1a022d` FOREIGN KEY (`user_id`) REFERENCES `{$this->getTable('admin/user')}` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

";
$installer->run($sql);

$installer->endSetup();
