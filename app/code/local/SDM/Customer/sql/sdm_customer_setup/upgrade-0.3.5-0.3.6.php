<?php
/**
 * Separation Degrees One
 *
 * Ellison's Mage_Customer customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Customer
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$this->addAttribute(
    'customer',
    'internal_notes',
    array(
        'input' => 'textarea',
        'type' => 'text',
        'label' => 'Internal Notes',
        'is_visible' => 0,
        'required' => 0,
        'user_defined' => 1,
        'note' => 'These notes are for administrative purposes only. They are not shown to the customer.'
    )
);
