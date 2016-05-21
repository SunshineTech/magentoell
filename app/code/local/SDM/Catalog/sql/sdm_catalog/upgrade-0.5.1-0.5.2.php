<?php
/**
 * Separation Degrees One
 *
 * Magento catalog customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Catalog
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$this->startSetup();

$this->updateAttribute('catalog_product', 'button_display_logic', 'note', 'This value is controlled by the current lifecycle settings and cannot be modified manually.');

$this->endSetup();

// Attribute "price_euro" and "special_price_euro" were created and added to the
// price group in all attribute sets
// @see SDM_Catalog_Helper_Data::EURO_PRICE_ATTRIBUTE_CODE
