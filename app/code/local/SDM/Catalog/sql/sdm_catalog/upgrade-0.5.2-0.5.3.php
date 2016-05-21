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

$this->updateAttribute('catalog_product', 'related_accessories', 'note', 'Separate attribtues by commas. To display featured attributes, use a | as the delimiter.<br><br>For example, enter "sku1,sku2|sku3,sku4,sku5" to display sku1 and sku2 as featured accessories, and to show all 5 skus in the accessories listing.');

$this->endSetup();
