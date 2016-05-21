<?php
/**
 * Separation Degrees One
 *
 * Magento catalog Update Attribute (masterqty)
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_MasterQty
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2016 Separation Degrees One (http://www.separationdegrees.com)
 */

$this->startSetup();

$this->updateAttribute('catalog_product', 'masterqty', array(
    'is_required' => false,
));

$this->endSetup();
