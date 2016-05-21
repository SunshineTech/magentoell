<?php
/**
 * DB update script for adding a unique key on pair in tieredcoupon_coupon table
 *
 * @copyright MexBS
 * @author MexBS <it@mexbs.com>
 */

$installer = $this;
/**
 * @var $installer Mage_Core_Model_Resource_Setup
 */

$installer->startSetup();

/**
 * alter table 'mexbs_tieredcoupon/tieredcoupon_coupon'
 */
$installer->getConnection()->addIndex(
    $installer->getTable('mexbs_tieredcoupon/tieredcoupon_coupon'),
    $installer->getIdxName(
        'mexbs_tieredcoupon/tieredcoupon_coupon',
        array('tieredcoupon_id', 'coupon_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('tieredcoupon_id', 'coupon_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$this->endSetup();