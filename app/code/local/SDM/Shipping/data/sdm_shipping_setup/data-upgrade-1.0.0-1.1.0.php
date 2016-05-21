<?php
/**
 * Separation Degrees One
 *
 * Ellison and Sizzix Shipping Rules
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Shipping
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$states = Mage::getModel('directory/region')->getCollection()
    ->addFieldToFilter('code', array('in' => array(
        'AS',
        'AF',
        'AC',
        'AM',
        'FM',
        'GU',
        'MH',
        'MP',
        'PW',
        'PR',
        'VI',
    )));

foreach ($states as $state) {
    $state->delete();
}
