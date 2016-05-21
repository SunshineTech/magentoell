<?php
/**
 * Separation Degrees One
 *
 * Lyris Newsletter Management
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Lyris
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * @var Mage_Core_Model_Resource_Setup $this
 */

$config = Mage::getConfig();

// already encrypted
$config->saveConfig('sdm_lyris/api/password', 'nrKTBm/QgtYyqFQ0/roQGw==', 'default', 0);

$websites = array(
    // sizzix_us
    1 => array(
        'sdm_lyris/api/site_id'         => 2012000352,
        'sdm_lyris/api/mlid'            => 1742,
        'sdm_lyris/popup/active'        => 1,
        'sdm_lyris/account/active'      => 1,
        'sdm_lyris/newsletter/name'     => 'Sizzix Scoop Newsletter',
    ),
    // sizzix_uk
    3 => array(
        'sdm_lyris/api/site_id'         => 2012000355,
        'sdm_lyris/api/mlid'            => 5119,
        'sdm_lyris/account/active'      => 1,
        'sdm_lyris/newsletter/name'     => 'Sizzix Scoop Newsletter',
    ),
    // ellison_retail
    4 => array(
        'sdm_lyris/api/site_id'     => 2012000343,
        'sdm_lyris/api/mlid'        => 10783,
        'sdm_lyris/account/active'  => 1,
        'sdm_lyris/newsletter/name' => 'Ellison Newsletter',
    ),
    // ellison_edu
    5 => array(
        'sdm_lyris/api/site_id'     => 2012000356,
        'sdm_lyris/api/mlid'        => 1656,
        'sdm_lyris/popup/active'    => 1,
        'sdm_lyris/account/active'  => 1,
        'sdm_lyris/newsletter/name' => 'Ellison Newsletter',
    ),
);

foreach ($websites as $id => $data) {
    foreach ($data as $path => $value) {
        $config->saveConfig($path, $value, 'websites', $id);
    }
}
