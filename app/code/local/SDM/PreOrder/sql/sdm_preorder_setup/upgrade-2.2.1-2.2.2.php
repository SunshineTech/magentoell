<?php
/**
 * Separation Degrees One
 *
 * Pre Order Module
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_PreOrder
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/* @var $installer Mage_Sales_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sales/quote_item'),
        'pre_order_approved',
        array(
            'type'    => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'comment' => 'Pre-Order Approved Override'
        )
    );

$installer->endSetup();
