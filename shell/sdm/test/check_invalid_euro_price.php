<?php
/**
 * SDM price check
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Shell
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'abstract.php';

/**
 * SDM_Shell_CheckInvalidEuroPrice
 */
class SDM_Shell_CheckInvalidEuroPrice extends Mage_Shell_Abstract
{
    /**
     * Log file
     *
     * @var string
     */
    protected $_logFileName = 'sdm_invalid_euro_prices.log';

    /**
     * Checks to see if there are any indexed simple products with missing
     * products.
     *
     * @return void
     */
    public function run()
    {
        $time = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));

        $q = "SELECT f.entity_id, f.sku, f.price,f.price_euro
            FROM catalog_product_flat_4 AS f
            WHERE f.visibility = 4
                AND f.type_id = 'simple'
                AND (f.price_euro = 0 OR f.price_euro IS NULL)";

        $results = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($q);

        if ($results) {
            Mage::log('*******************************************', null, $this->_logFileName);
            Mage::log("Invalid Euro prices detected!", null, $this->_logFileName);
            Mage::log("Local time: $time", null, $this->_logFileName);
            foreach ($results as $one) {
                Mage::log(
                    "{$one['entity_id']} \t {$one['sku']} \t {$one['price']} \t {$one['price_euro']}",
                    null,
                    $this->_logFileName
                );
            }

            Mage::log('*******************************************', null, $this->_logFileName);
        } else {
            Mage::log("Ran at local time: $time", null, $this->_logFileName);
        }
    }
}

$shell = new SDM_Shell_CheckInvalidEuroPrice();
$shell->run();
