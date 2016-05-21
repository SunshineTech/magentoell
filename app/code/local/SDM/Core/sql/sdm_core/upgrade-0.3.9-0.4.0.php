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

$html = '<p><img alt="" src="{{media url="wysiwyg/sizzix_101_1.png"}}" /></p>
<div class="larger-half-col">
<h3>Frequently Asked Questions:</h3>
<ul>
<li><a href="#">Example Link</a></li>
<li><a href="#">Example Link</a></li>
<li><a href="#">Example Link</a></li>
<li><a href="#">Example Link</a></li>
<li><a href="#">Example Link</a></li>
</ul>
</div>
<div class="smaller-half-col">
<h3>General Support:</h3>
<ul>
<li><a href="#">Example Link</a></li>
<li><a href="#">Example Link</a></li>
<li><a href="#">Example Link</a></li>
<li><a href="#">Example Link</a></li>
<li><a href="#">Example Link</a></li>
</ul>
</div>';

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
        ->setTitle("Contact Page Sidebar (".$storeLabel.")")
        ->setIdentifier("contact_page_sidebar")
        ->setContent($html)
        ->save();
}
