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

$this->updateAttribute(
    'catalog_product',
    'instruction_file',
    'note',
    '<span class="inst-products">Recommended upload location for products:<br>' .
    'uploads/pdfs/instructions/products/*.pdf</span>' .
    '<span class="inst-ideas">Recommended upload location for ideas:<br>' .
    'uploads/pdfs/instructions/ideas/*.pdf</span>'
);

$this->endSetup();
