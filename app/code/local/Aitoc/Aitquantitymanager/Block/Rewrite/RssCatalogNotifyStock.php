<?php
/**
 * Multi-Location Inventory
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitquantitymanager
 * @version      2.2.3
 * @license:     rJhV4acfvLy4sPgpe7MoLJnfOEhDVfWVuKRvbpcv30
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitquantitymanager_Block_Rewrite_RssCatalogNotifyStock extends Mage_Rss_Block_Catalog_NotifyStock
{
    public function addNotifyItemXmlCallback($args)
    {
        $product = $args['product'];
        $product->setData($args['row']);
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/edit/',
            array('id' => $product->getId(), '_secure' => true, '_nosecret' => true));
        $qty = 1 * $product->getQty();
        $description = Mage::helper('rss')->__('%s has reached a quantity of %s.', $product->getName(), $qty);
        $rssObj = $args['rssObj'];
        if(Mage::getModel('core/website')->load($args['row']['website_id'])->getId() > 0)
        {
            $storeId = Mage::getModel('core/website')->load($args['row']['website_id'])->getDefaultStore()->getId();
            $websiteName = Mage::getModel('core/website')->load($args['row']['website_id'])->getName();
            $url .='store/'.$storeId.'/';
            $description = $args['row']['low_stock_date'].': '.$description.' (website name: "'.$websiteName.'")';
        }
        $data = array(
            'title'         => $product->getName(),
            'link'          => $url,
            'description'   => $description,
        );
        $rssObj->_addEntry($data);
    }
}