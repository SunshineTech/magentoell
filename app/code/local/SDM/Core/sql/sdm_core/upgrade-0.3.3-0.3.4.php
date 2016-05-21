<?php
/**
 * Separation Degrees Media
 *
 * SDM's core extension
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Core
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

// Remove uneeded attributes
$this->removeAttribute('catalog_product', 'virtual_weight');
$this->removeAttribute('catalog_product', 'virtual_weight_end_date');
