<?php
/**
 * Separation Degrees One
 *
 * eClips Software Download
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Eclips
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

// Delete all existing data, just in case
$collection = Mage::getModel('eclips/request')->getCollection();

foreach ($collection as $request) {
    $request->delete();
}

// Save the an entry to start counting
Mage::getModel('eclips/request')->setCount(0)
    ->save();
