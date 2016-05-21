<?php
/**
 * Separation Degrees One
 *
 * Valutec Giftcard Integration
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Valutec
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sales/order_grid'),
        'sdm_valutec_giftcard_identifier',
        array(
            'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'  => 10,
            'comment' => 'SDM Valutec Giftcard Identifier'
        )
    );

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sales/order'),
        'sdm_valutec_giftcard_identifier',
        array(
            'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'  => 10,
            'comment' => 'SDM Valutec Giftcard Identifier'
        )
    );

$installer->endSetup();
