<?php
/**
 * Separation Degrees Media
 *
 * Ellison's custom product taxonomy implementation.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Taxonomy
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

$this->startSetup();

$installer = new Mage_Catalog_Model_Resource_Setup();

$installer->updateAttribute(
    'catalog_product',
    'tag_artist',
    array('source_model' => 'taxonomy/attribute_source_artist')
);

$installer->updateAttribute(
    'catalog_product',
    'tag_designer',
    array('source_model' => 'taxonomy/attribute_source_designer')
);

$this->endSetup();
