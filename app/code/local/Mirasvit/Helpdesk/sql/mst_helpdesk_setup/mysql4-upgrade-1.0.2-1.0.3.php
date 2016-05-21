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
if ($version == '1.0.3') {
    return;
} elseif ($version != '1.0.2') {
    die('Please, run migration Helpdesk 1.0.2');
}
$installer->startSetup();
$sql = "
ALTER TABLE `{$this->getTable('helpdesk/ticket')}` ADD COLUMN `is_archived` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `{$this->getTable('helpdesk/ticket')}` ADD COLUMN `fp_period_unit` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `{$this->getTable('helpdesk/ticket')}` ADD COLUMN `fp_period_value` INT(11) ;
ALTER TABLE `{$this->getTable('helpdesk/ticket')}` ADD COLUMN `fp_execute_at` TIMESTAMP NULL;
ALTER TABLE `{$this->getTable('helpdesk/ticket')}` ADD COLUMN `fp_is_remind` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `{$this->getTable('helpdesk/ticket')}` ADD COLUMN `fp_remind_email` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `{$this->getTable('helpdesk/ticket')}` ADD COLUMN `fp_priority_id` INT(11);
ALTER TABLE `{$this->getTable('helpdesk/ticket')}` ADD COLUMN `fp_status_id` INT(11);
ALTER TABLE `{$this->getTable('helpdesk/ticket')}` ADD COLUMN `fp_department_id` INT(11);
ALTER TABLE `{$this->getTable('helpdesk/ticket')}` ADD COLUMN `fp_user_id` ".(Mage::getVersion() >= '1.6.0.0' ? 'int(10)' : 'mediumint(11)').' unsigned;
';
$installer->run($sql);

/*                                    **/

$installer->endSetup();
