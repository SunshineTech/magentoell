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
if ($version == '1.0.18') {
    return;
} elseif ($version != '1.0.17') {
    die('Please, run migration Helpdesk 1.0.17');
}
$installer->startSetup();
if (Mage::registry('mst_allow_drop_tables')) {
    $sql = "
       DROP TABLE IF EXISTS `{$this->getTable('helpdesk/draft')}`;
    ";
    $installer->run($sql);
}
$sql = "
CREATE TABLE IF NOT EXISTS `{$this->getTable('helpdesk/draft')}` (
    `draft_id` int(11) NOT NULL AUTO_INCREMENT,
    `ticket_id` INT(11) NOT NULL,
    `users_online` VARCHAR(255) NOT NULL DEFAULT '',
    `body` TEXT,
    `updated_by` INT(11) ,
    `updated_at` TIMESTAMP NULL,
    KEY `fk_helpdesk_draft_ticket_id` (`ticket_id`),
    CONSTRAINT `mst_d6b6d0ec1c37766411b62bbf43a7de0d` FOREIGN KEY (`ticket_id`) REFERENCES `{$this->getTable('helpdesk/ticket')}` (`ticket_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (`draft_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

";
$installer->run($sql);

/*                                    **/

$installer->endSetup();
