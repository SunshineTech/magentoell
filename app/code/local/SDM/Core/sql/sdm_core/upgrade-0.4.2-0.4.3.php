<?php
/**
 * Separation Degrees Media
 *
 * SDM's core extension
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Core
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */


$eavConfig = Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'institution')
    ->setData('used_in_forms', array('adminhtml_customer'))
    ->save();

$eavConfig = Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'institution_description')
    ->setData('used_in_forms', array('adminhtml_customer'))
    ->save();

$eavConfig = Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'company')
    ->setData('used_in_forms', array('adminhtml_customer'))
    ->save();
