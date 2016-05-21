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

// Delete "Footer Links"
$oldFooterLinksBlock = Mage::getModel('cms/block')->load(1)->delete();

// Copy HTML from "Footer Links for US" and then delete
$oldFooterLinksBlock = Mage::getModel('cms/block')->load(2);
$footerLinksContent = $oldFooterLinksBlock->getContent();
$oldFooterLinksBlock->delete();

// Copy HTML from "Footer Links Copyright" for ERUS and EEUS, and then delete
$oldFooterCopyright = Mage::getModel('cms/block')->load(14);
$footerLinksCopyright = $oldFooterCopyright->getContent();
$oldFooterCopyright->delete();

// Create new Footer Links Copyright for EEUS and ERUS
foreach (array(5 => 'ERUS', 6 => 'EEUS') as $storeId => $storeLabel) {
    Mage::getModel('cms/block')
        ->setStores(array($storeId))
        ->setTitle("Footer Links Copyright (".$storeLabel.")")
        ->setIdentifier("footer_links_copyright")
        ->setContent($footerLinksCopyright)
        ->save();
}

// Create new Footer HTML for all sites
$storeIdArray = array(
    'SZUS' => array(1),
    'SZUK' => array(7,4),
    'ERUS' => array(5),
    'EEUS' => array(6)
);
foreach ($storeIdArray as $storeLabel => $storeIds) {
    Mage::getModel('cms/block')
        ->setStores($storeIds)
        ->setTitle("Footer Links (".$storeLabel.")")
        ->setIdentifier("footer_links")
        ->setContent($footerLinksContent)
        ->save();
}

// Rename old footer links copyright
Mage::getModel('cms/block')
    ->load(4)
    ->setTitle('Footer Links Copyright (SZUS)')
    ->save();
Mage::getModel('cms/block')
    ->load(13)
    ->setTitle('Footer Links Copyright (SZUK)')
    ->save();
