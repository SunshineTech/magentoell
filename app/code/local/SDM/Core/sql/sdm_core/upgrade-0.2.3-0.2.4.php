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

$this->updateAttribute('catalog_product', 'idea_instructions', array(
    'input' => 'textarea',
    'is_wysiwyg_enabled' => 1,
    'is_html_allowed_on_front' => 1
));

$this->updateAttribute('catalog_product', 'idea_standards', array(
    'input' => 'textarea',
    'is_wysiwyg_enabled' => 1,
    'is_html_allowed_on_front' => 1
));

$this->updateAttribute('catalog_product', 'idea_introduction', array(
    'input' => 'textarea',
    'is_wysiwyg_enabled' => 1,
    'is_html_allowed_on_front' => 1
));

$this->removeAttribute('catalog_product', 'introduction');
