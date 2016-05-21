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

$installer
    ->addAttribute('quote',         'sdm_valutec_giftcard',               array('type' => 'text'))
    ->addAttribute('quote',         'sdm_valutec_giftcard_amount',        array('type' => 'decimal'))
    ->addAttribute('quote',         'base_sdm_valutec_giftcard_amount',   array('type' => 'decimal'))
    ->addAttribute('quote_address', 'sdm_valutec_giftcard_amount',        array('type' => 'decimal'))
    ->addAttribute('quote_address', 'base_sdm_valutec_giftcard_amount',   array('type' => 'decimal'))
    ->addAttribute('order',         'sdm_valutec_giftcard',               array('type' => 'text'))
    ->addAttribute('order',         'sdm_valutec_giftcard_amount',        array('type' => 'decimal'))
    ->addAttribute('order',         'base_sdm_valutec_giftcard_amount',   array('type' => 'decimal'))
    ->addAttribute('order',         'sdm_valutec_giftcard_refunded',      array('type' => 'decimal'))
    ->addAttribute('order',         'base_sdm_valutec_giftcard_refunded', array('type' => 'decimal'))
    ->addAttribute('invoice',       'sdm_valutec_giftcard_amount',        array('type' => 'decimal'))
    ->addAttribute('invoice',       'base_sdm_valutec_giftcard_amount',   array('type' => 'decimal'))
    ->addAttribute('creditmemo',    'sdm_valutec_giftcard_amount',        array('type' => 'decimal'))
    ->addAttribute('creditmemo',    'base_sdm_valutec_giftcard_amount',   array('type' => 'decimal'));

$installer->endSetup();
