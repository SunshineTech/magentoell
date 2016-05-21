<?php
/**
 * Separation Degrees Media
 *
 * Install attribute sets, groups, and assign attributes
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Migration
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2014 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * Some of the taxonomy items made into regular attributes are now becoming
 * Magento taxonomy items due to the global scope change.
 */
$this->updateAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'tag_material_compatibility',
    array('source_model' => 'taxonomy/attribute_source_materialcompatibility')
);

$this->updateAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'tag_machine_compatibility',
    array('source_model' => 'taxonomy/attribute_source_machinecompatibility')
);
