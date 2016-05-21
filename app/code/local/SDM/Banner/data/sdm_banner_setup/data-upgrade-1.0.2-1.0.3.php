<?php
/**
 * Separation Degrees One
 *
 * Banner Ads
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Banner
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$collection = Mage::getModel('slider/layouts')->getCollection()
    ->addFieldToFilter('layout_handle', 'calendar_index_index');

foreach ($collection as $layout) {
    $layout = Mage::getModel('slider/layouts')->load($layout->getId());
    $layout->setLayoutHandle('sdm_calendar_event_list')
        ->save();
}
